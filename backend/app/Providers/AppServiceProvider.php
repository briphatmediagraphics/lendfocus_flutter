<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {
            $lang = \App\Support\Translator::lang();
            $view->with('lang', $lang);
            $view->with('t', config("translations.{$lang}", []));
            $view->with('company', config('landfocus.company'));
            $view->with('social', config('landfocus.social'));
            $view->with('isAdmin', session('is_admin', false));
        });
    }
}
