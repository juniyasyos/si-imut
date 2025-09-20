<?php

namespace App\Providers;

use App\Console\Commands\OptimizeCacheCommand;
use App\Services\Cache\SimpleCacheOptimizer;
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
        // Register cache optimizer
        $this->app->singleton(SimpleCacheOptimizer::class, function ($app) {
            return new SimpleCacheOptimizer();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                OptimizeCacheCommand::class,
            ]);
        }
    }
}
