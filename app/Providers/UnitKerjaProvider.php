<?php

namespace App\Providers;

use App\Repositories\Interfaces\UnitKerjaFolderRepositoryInterface;
use App\Repositories\UnitKerjaFolderRepository;
use Illuminate\Support\ServiceProvider;

class UnitKerjaProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            UnitKerjaFolderRepositoryInterface::class,
            UnitKerjaFolderRepository::class,
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