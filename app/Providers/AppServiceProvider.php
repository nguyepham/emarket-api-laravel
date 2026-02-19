<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Define a custom limiter named 'password-reset'
        RateLimiter::for('password-reset', function (Request $request) {
            // Allow 3 attempts per minute, keyed by the user's IP address
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}
