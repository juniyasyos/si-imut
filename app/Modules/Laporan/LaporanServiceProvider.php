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
    }

    public function boot(): void
    {
        //
    }
}
