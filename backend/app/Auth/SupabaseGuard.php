<?php

namespace App\Auth;

use App\Models\User;
use App\Services\SupabaseAuthService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class SupabaseGuard implements Guard
{
    protected ?Authenticatable $user = null;
    protected ?array $claims = null;

    public function __construct(protected SupabaseAuthService $authService, protected Request $request)
    {
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $accessToken = $this->request->cookie('sb-access');
        if (! $accessToken) {
            return null;
        }

        $claims = $this->authService->verifyJwt($accessToken);
        if (! $claims || empty($claims['sub'])) {
            return $this->refreshAccessToken();
        }

        if (! $this->validateClaims($claims)) {
            return null;
        }

        $this->claims = $claims;
        $this->user = User::find($claims['sub']);

        return $this->user;
    }

    protected function refreshAccessToken(): ?Authenticatable
    {
        $refreshToken = $this->request->cookie('sb-refresh');
        if (! $refreshToken) {
            return null;
        }

        $response = $this->authService->refresh($refreshToken);
        if (! $response || empty($response['access_token']) || empty($response['refresh_token'])) {
            return null;
        }

        $accessToken = $response['access_token'];
        $refreshToken = $response['refresh_token'];

        Cookie::queue(cookie('sb-access', $accessToken, 30, '/', null, app()->environment('production'), true, true, false, 'Lax'));
        Cookie::queue(cookie('sb-refresh', $refreshToken, 43200, '/', null, app()->environment('production'), true, true, false, 'Lax'));

        $claims = $this->authService->verifyJwt($accessToken);
        if (! $claims || empty($claims['sub'])) {
            return null;
        }

        if (! $this->validateClaims($claims)) {
            return null;
        }

        $this->claims = $claims;
        $this->user = User::find($claims['sub']);

        return $this->user;
    }

    protected function validateClaims(array $claims): bool
    {
        return isset($claims['iss'], $claims['sub'], $claims['exp'])
            && $claims['iss'] === config('services.supabase.jwt_issuer')
            && now()->timestamp < (int) $claims['exp'];
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function id(): ?string
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function logout(): void
    {
        $refreshToken = $this->request->cookie('sb-refresh');
        if ($refreshToken && method_exists($this->authService, 'logout')) {
            $this->authService->logout($refreshToken);
        }

        Cookie::queue(Cookie::forget('sb-access'));
        Cookie::queue(Cookie::forget('sb-refresh'));

        $this->user = null;
        $this->claims = null;
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }
}
