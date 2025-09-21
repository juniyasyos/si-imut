<?php

namespace App\Providers;

use App\Services\LaporanImut\LaporanImutService;
use Illuminate\Support\ServiceProvider;

class LaporanImutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
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
