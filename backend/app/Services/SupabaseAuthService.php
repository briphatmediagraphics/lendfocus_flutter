<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SupabaseAuthService
{
    public function authUrl(): string
    {
        return rtrim(config('services.supabase.url'), '/') . '/auth/v1';
    }

    public function login(string $email, string $password): ?array
    {
        $response = Http::withHeaders([
            'apikey' => config('services.supabase.anon_key'),
            'Content-Type' => 'application/json',
        ])->post($this->authUrl() . '/token?grant_type=password', [
            'email' => $email,
            'password' => $password,
        ]);

        return $response->successful() ? $response->json() : null;
    }

    public function refresh(string $refreshToken): ?array
    {
        $response = Http::withHeaders([
            'apikey' => config('services.supabase.anon_key'),
            'Content-Type' => 'application/json',
        ])->post($this->authUrl() . '/token?grant_type=refresh_token', [
            'refresh_token' => $refreshToken,
        ]);

        return $response->successful() ? $response->json() : null;
    }

    public function logout(string $refreshToken): bool
    {
        $response = Http::withHeaders([
            'apikey' => config('services.supabase.anon_key'),
            'Content-Type' => 'application/json',
        ])->post($this->authUrl() . '/logout', [
            'refresh_token' => $refreshToken,
        ]);

        return $response->successful();
    }

    public function verifyJwt(string $token): ?array
    {
        [$headerEncoded, $payloadEncoded, $signatureEncoded] = explode('.', $token) + [null, null, null];
        if (! $headerEncoded || ! $payloadEncoded || ! $signatureEncoded) {
            return null;
        }

        $headerJson = $this->base64UrlDecode($headerEncoded);
        $payloadJson = $this->base64UrlDecode($payloadEncoded);
        $signature = $this->base64UrlDecode($signatureEncoded);

        if ($headerJson === false || $payloadJson === false || $signature === false) {
            return null;
        }

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);
        if (! is_array($header) || ! is_array($payload) || empty($header['kid']) || $header['alg'] !== 'RS256') {
            return null;
        }

        $jwks = $this->getJwks();
        $key = $this->findJwk($jwks, $header['kid']);
        if (! $key) {
            return null;
        }

        $publicKey = $this->jwkToPem($key);
        $data = $headerEncoded . '.' . $payloadEncoded;

        if (openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256) !== 1) {
            return null;
        }

        return isset($payload['iss'], $payload['sub'], $payload['exp']) && $payload['iss'] === config('services.supabase.jwt_issuer')
            ? $payload
            : null;
    }

    protected function base64UrlDecode(string $value): string|false
    {
        $value = strtr($value, '-_', '+/');
        $value .= str_repeat('=', (4 - strlen($value) % 4) % 4);

        return base64_decode($value);
    }

    protected function findJwk(array $jwks, string $kid): ?array
    {
        if (empty($jwks['keys']) || ! is_array($jwks['keys'])) {
            return null;
        }

        foreach ($jwks['keys'] as $key) {
            if (! empty($key['kid']) && $key['kid'] === $kid) {
                return $key;
            }
        }

        return null;
    }

    protected function jwkToPem(array $jwk): string
    {
        if (empty($jwk['kty']) || $jwk['kty'] !== 'RSA' || empty($jwk['n']) || empty($jwk['e'])) {
            throw new \RuntimeException('Unsupported JWK format');
        }

        $modulus = $this->base64UrlDecode($jwk['n']);
        $exponent = $this->base64UrlDecode($jwk['e']);

        if ($modulus === false || $exponent === false) {
            throw new \RuntimeException('Invalid RSA key values');
        }

        $modulus = $this->encodeUnsignedInteger($modulus);
        $exponent = $this->encodeUnsignedInteger($exponent);

        $rsaPublicKey = $this->encodeSequence($modulus . $exponent);
        $bitString = "\x03" . $this->encodeLength(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey;
        $oid = "\x06\x09*\x86H\x86\xF7\x0D\x01\x01\x01";
        $sequence = $this->encodeSequence($this->encodeSequence($oid . "\x05\x00") . $bitString);

        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($sequence), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    protected function encodeLength(int $length): string
    {
        if ($length <= 0x7f) {
            return chr($length);
        }

        $temp = '';
        while ($length > 0) {
            $temp = chr($length & 0xff) . $temp;
            $length >>= 8;
        }

        return chr(0x80 | strlen($temp)) . $temp;
    }

    protected function encodeUnsignedInteger(string $value): string
    {
        if (ord($value[0]) > 0x7f) {
            $value = "\x00" . $value;
        }

        return "\x02" . $this->encodeLength(strlen($value)) . $value;
    }

    protected function encodeSequence(string $data): string
    {
        return "\x30" . $this->encodeLength(strlen($data)) . $data;
    }

    protected function getJwks(): array
    {
        return Cache::remember('supabase_jwks', 1440, function () {
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
            ])->get(rtrim(config('services.supabase.url'), '/') . '/auth/v1/.well-known/jwks.json');

            return $response->successful() ? $response->json() : [];
        });
    }
}
