<?php

namespace App\Providers;

use App\Services\LaporanImut\LaporanImutService;
use App\Services\LaporanImut\LaporanImutQueryService;
use App\Services\LaporanImut\LaporanImutCacheService;
use App\Services\LaporanImut\LaporanImutCalculationService;
use Illuminate\Support\ServiceProvider;

class LaporanImutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Register individual services
        $this->app->singleton(LaporanImutQueryService::class);
        $this->app->singleton(LaporanImutCacheService::class);
        $this->app->singleton(LaporanImutCalculationService::class);

        // Register main service with dependencies
        $this->app->singleton(LaporanImutService::class, function ($app) {
            return new LaporanImutService(
                $app->make(LaporanImutQueryService::class),
                $app->make(LaporanImutCacheService::class),
                $app->make(LaporanImutCalculationService::class)
            );
        });

        // Bind for facade (backward compatibility)
        $this->app->singleton('laporanimut', function ($app) {
            return $app->make(LaporanImutService::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
