<?php

namespace App\Providers;

use App\Auth\SupabaseGuard;
use App\Services\SupabaseAuthService;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SupabaseAuthService::class, function () {
            return new SupabaseAuthService();
        });
    }

    public function boot(): void
    {
        Auth::extend('supabase', function ($app, $name, array $config) {
            return new SupabaseGuard($app->make(SupabaseAuthService::class), $app->make('request'));
        });
    }
}
