<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Adapters\Filament\LaporanImutFilamentAdapter;
use App\Services\Facades\LaporanImutBusinessLogicService;
use App\Commands\LaporanImut\CreateLaporanImutCommand;
use App\Commands\LaporanImut\UpdateLaporanImutCommand;
use App\Commands\LaporanImut\DeleteLaporanImutCommand;
use App\Commands\LaporanImut\GetLaporanImutListCommand;

/**
 * Business Logic Service Provider
 *
 * Registers all business logic services and adapters
 */
class BusinessLogicServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Commands
        $this->app->bind(CreateLaporanImutCommand::class);
        $this->app->bind(UpdateLaporanImutCommand::class);
        $this->app->bind(DeleteLaporanImutCommand::class);
        $this->app->bind(GetLaporanImutListCommand::class);

        // Register Adapters
        $this->app->bind(LaporanImutFilamentAdapter::class);

        // Register Business Logic Service for Facade
        $this->app->bind('laporan-imut-business-logic', function ($app) {
            return new LaporanImutBusinessLogicService(
                $app->make(LaporanImutFilamentAdapter::class)
            );
        });

        // Alias for easier access
        $this->app->alias('laporan-imut-business-logic', LaporanImutBusinessLogicService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'laporan-imut-business-logic',
            LaporanImutBusinessLogicService::class,
            LaporanImutFilamentAdapter::class,
            CreateLaporanImutCommand::class,
            UpdateLaporanImutCommand::class,
            DeleteLaporanImutCommand::class,
            GetLaporanImutListCommand::class,
        ];
    }
}
