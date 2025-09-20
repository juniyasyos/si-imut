<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Services\Security\SecurityMonitoringService;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SecurityMonitoringService::class, function ($app) {
            return new SecurityMonitoringService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->registerSecurityMiddleware();
    }

    /**
     * Configure rate limiting.
     */
    protected function configureRateLimiting(): void
    {
        // API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Authentication rate limiting
        RateLimiter::for('auth', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(20)->by($request->ip()),
            ];
        });

        // File upload rate limiting
        RateLimiter::for('uploads', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(100)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Strict rate limiting for sensitive operations
        RateLimiter::for('strict', function (Request $request) {
            return [
                Limit::perMinute(2)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(10)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Guest rate limiting
        RateLimiter::for('guest', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // Search rate limiting
        RateLimiter::for('search', function (Request $request) {
            return [
                Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(200)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Password reset rate limiting
        RateLimiter::for('password-reset', function (Request $request) {
            return [
                Limit::perMinute(1)->by($request->input('email')),
                Limit::perHour(5)->by($request->input('email')),
                Limit::perDay(10)->by($request->input('email')),
            ];
        });

        // Registration rate limiting
        RateLimiter::for('registration', function (Request $request) {
            return [
                Limit::perMinute(2)->by($request->ip()),
                Limit::perHour(10)->by($request->ip()),
                Limit::perDay(20)->by($request->ip()),
            ];
        });

        // Contact form rate limiting
        RateLimiter::for('contact', function (Request $request) {
            return [
                Limit::perMinute(1)->by($request->ip()),
                Limit::perHour(5)->by($request->ip()),
                Limit::perDay(10)->by($request->ip()),
            ];
        });

        // Admin operations rate limiting
        RateLimiter::for('admin', function (Request $request) {
            return [
                Limit::perMinute(100)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(1000)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Report generation rate limiting
        RateLimiter::for('reports', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(30)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Export rate limiting
        RateLimiter::for('exports', function (Request $request) {
            return [
                Limit::perMinute(2)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(10)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Bulk operations rate limiting
        RateLimiter::for('bulk', function (Request $request) {
            return [
                Limit::perMinute(1)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(5)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // WebSocket connection rate limiting
        RateLimiter::for('websocket', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(100)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Health check rate limiting (very permissive)
        RateLimiter::for('health', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Public API rate limiting
        RateLimiter::for('public-api', function (Request $request) {
            $key = $request->header('X-API-Key') ?: $request->ip();
            return [
                Limit::perMinute(100)->by($key),
                Limit::perHour(1000)->by($key),
                Limit::perDay(10000)->by($key),
            ];
        });
    }

    /**
     * Register security middleware.
     */
    protected function registerSecurityMiddleware(): void
    {
        $router = $this->app['router'];

        // Register middleware aliases
        $router->aliasMiddleware('enhanced.rate.limiting', \App\Http\Middleware\EnhancedRateLimitingMiddleware::class);
        $router->aliasMiddleware('sanitize.input', \App\Http\Middleware\SanitizeInputMiddleware::class);
        $router->aliasMiddleware('sql.injection.protection', \App\Http\Middleware\SqlInjectionProtectionMiddleware::class);

        // Register middleware groups
        $router->middlewareGroup('security', [
            'enhanced.rate.limiting',
            'sanitize.input',
            'sql.injection.protection',
        ]);

        $router->middlewareGroup('api.security', [
            'enhanced.rate.limiting:api',
            'sanitize.input',
            'sql.injection.protection',
        ]);

        $router->middlewareGroup('admin.security', [
            'enhanced.rate.limiting:admin',
            'sanitize.input',
            'sql.injection.protection',
        ]);

        $router->middlewareGroup('auth.security', [
            'enhanced.rate.limiting:auth',
            'sanitize.input',
            'sql.injection.protection',
        ]);

        $router->middlewareGroup('guest.security', [
            'enhanced.rate.limiting:guest',
            'sanitize.input',
            'sql.injection.protection',
        ]);
    }
}
