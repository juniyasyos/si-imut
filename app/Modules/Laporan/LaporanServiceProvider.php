<?php

namespace App\Modules\Laporan;

use Illuminate\Support\ServiceProvider;

class LaporanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            Contracts\LaporanInterface::class,
            Services\LaporanService::class
        );
        $this->loadViewsFrom(__DIR__.'/Resources/Views', 'laporan');
    }

    public function boot(): void
    {
        //
    }
}
