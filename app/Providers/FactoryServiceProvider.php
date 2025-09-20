<?php

namespace App\Providers;

use App\Factories\LaporanImutFactory;
use App\Factories\ImutProfileFactory;
use Illuminate\Support\ServiceProvider;

class FactoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register factory singletons
        $this->app->singleton(LaporanImutFactory::class);
        $this->app->singleton(ImutProfileFactory::class);

        // Register strategy context
        $this->app->singleton(\App\Strategies\CalculationContext::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
