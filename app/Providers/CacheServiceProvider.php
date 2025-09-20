<?php

namespace App\Providers;

use App\Services\Cache\CacheManager;
use App\Services\Cache\ImutDataCacheService;
use App\Services\Cache\LaporanImutCacheService;
use App\Services\Cache\UserCacheService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Cache Service Provider
 *
 * Registers all cache services and sets up model event listeners
 * for automatic cache invalidation.
 */
class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register individual cache services
        $this->app->singleton(LaporanImutCacheService::class);
        $this->app->singleton(ImutDataCacheService::class);
        $this->app->singleton(UserCacheService::class);

        // Register cache manager
        $this->app->singleton(CacheManager::class, function ($app) {
            return new CacheManager(
                $app->make(LaporanImutCacheService::class),
                $app->make(ImutDataCacheService::class),
                $app->make(UserCacheService::class)
            );
        });

        // Register cache manager alias
        $this->app->alias(CacheManager::class, 'cache.manager');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerModelEventListeners();
        $this->registerConsoleCommands();
    }

    /**
     * Register model event listeners for automatic cache invalidation
     */
    private function registerModelEventListeners(): void
    {
        $cacheManager = $this->app->make(CacheManager::class);

        // LaporanImut events
        Event::listen('eloquent.created: App\Models\LaporanImut', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('LaporanImut', 'created', $model);
        });

        Event::listen('eloquent.updated: App\Models\LaporanImut', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('LaporanImut', 'updated', $model);
        });

        Event::listen('eloquent.deleted: App\Models\LaporanImut', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('LaporanImut', 'deleted', $model);
        });

        // ImutData events
        Event::listen('eloquent.created: App\Models\ImutData', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('ImutData', 'created', $model);
        });

        Event::listen('eloquent.updated: App\Models\ImutData', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('ImutData', 'updated', $model);
        });

        Event::listen('eloquent.deleted: App\Models\ImutData', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('ImutData', 'deleted', $model);
        });

        // ImutProfile events
        Event::listen('eloquent.created: App\Models\ImutProfile', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('ImutProfile', 'created', $model);
        });

        Event::listen('eloquent.updated: App\Models\ImutProfile', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('ImutProfile', 'updated', $model);
        });

        Event::listen('eloquent.deleted: App\Models\ImutProfile', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('ImutProfile', 'deleted', $model);
        });

        // User events
        Event::listen('eloquent.created: App\Models\User', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('User', 'created', $model);
        });

        Event::listen('eloquent.updated: App\Models\User', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('User', 'updated', $model);
        });

        Event::listen('eloquent.deleted: App\Models\User', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('User', 'deleted', $model);
        });

        // UnitKerja events
        Event::listen('eloquent.created: App\Models\UnitKerja', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('UnitKerja', 'created', $model);
        });

        Event::listen('eloquent.updated: App\Models\UnitKerja', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('UnitKerja', 'updated', $model);
        });

        Event::listen('eloquent.deleted: App\Models\UnitKerja', function ($model) use ($cacheManager) {
            $cacheManager->handleModelEvent('UnitKerja', 'deleted', $model);
        });
    }

    /**
     * Register console commands for cache management
     */
    private function registerConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\CacheWarmUpCommand::class,
                \App\Console\Commands\CacheHealthCheckCommand::class,
                \App\Console\Commands\CacheOptimizeCommand::class,
                \App\Console\Commands\CacheStatsCommand::class,
            ]);
        }
    }
}
