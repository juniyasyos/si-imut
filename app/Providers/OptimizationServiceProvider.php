<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Simple Optimization Service Provider
 * Basic optimization services for internal company app
 */
class OptimizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Simplified - removed complex cache optimizers
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Simplified - removed complex optimization commands
    }
}
