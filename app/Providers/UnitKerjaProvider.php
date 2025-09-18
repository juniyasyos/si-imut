<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class UnitKerjaProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            \App\Repositories\Interfaces\UnitKerjaFolderRepositoryInterface::class,
            \App\Repositories\UnitKerjaFolderRepository::class,
        );

        $this->app->bind(
            \App\Repositories\Interfaces\ImutDataRepositoryInterface::class,
            \App\Repositories\ImutDataRepository::class,
        );

        $this->app->bind(
            \App\Repositories\Interfaces\LaporanImutRepositoryInterface::class,
            \App\Repositories\LaporanImutRepository::class,
        );

        $this->app->bind(
            \App\Repositories\Interfaces\ImutProfileRepositoryInterface::class,
            \App\Repositories\ImutProfileRepository::class,
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
