<?php

namespace App\Modules\ImutMaster;

use Illuminate\Support\ServiceProvider;

class ImutMasterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            Contracts\ImutMasterInterface::class,
            Services\ImutMasterService::class
        );
    }

    public function boot(): void
    {
        //
    }
}
