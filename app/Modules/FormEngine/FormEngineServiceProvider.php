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
        $this->loadViewsFrom(__DIR__.'/Resources/Views', 'form-engine');
    }

    public function boot(): void
    {
        //
    }
}
