<?php

namespace App\Modules\FormEngine;

use Illuminate\Support\ServiceProvider;

class FormEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            Contracts\FormEngineInterface::class,
            Services\FormEngineService::class
        );
    }

    public function boot(): void
    {
        //
    }
}
