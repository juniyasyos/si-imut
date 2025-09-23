<?php

namespace App\Providers;

use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use App\Repositories\ImutDataRepository;
use App\Services\ImutDataService;
use App\Services\Filament\ImutDataFilamentService;
use App\Services\Filament\Widgets\DashboardWidgetService;
use App\Services\Filament\Widgets\LaporanWidgetService;
use App\Services\Filament\Widgets\ImutCapaianWidgetService;
use App\Services\Filament\Widgets\ImutCapaianAllUnitWidgetService;
use App\Services\DashboardImutService;
use App\Services\ImutChartSeriesService;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            ImutDataRepositoryInterface::class,
            ImutDataRepository::class
        );

        // Service bindings
        $this->app->bind(ImutDataService::class, function ($app) {
            return new ImutDataService(
                $app->make(ImutDataRepositoryInterface::class)
            );
        });

        // Filament Service bindings
        $this->app->bind(ImutDataFilamentService::class, function ($app) {
            return new ImutDataFilamentService(
                $app->make(ImutDataService::class)
            );
        });

        // Widget Service bindings
        $this->app->bind(DashboardWidgetService::class, function ($app) {
            return new DashboardWidgetService(
                $app->make(DashboardImutService::class)
            );
        });

        $this->app->singleton(LaporanWidgetService::class);

        $this->app->bind(ImutCapaianWidgetService::class, function ($app) {
            return new ImutCapaianWidgetService(
                $app->make(ImutChartSeriesService::class)
            );
        });

        $this->app->bind(ImutCapaianAllUnitWidgetService::class, function ($app) {
            return new ImutCapaianAllUnitWidgetService(
                $app->make(ImutChartSeriesService::class)
            );
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
