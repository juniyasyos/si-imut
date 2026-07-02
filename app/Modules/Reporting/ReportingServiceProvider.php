<?php

namespace App\Modules\Reporting;

use Illuminate\Support\ServiceProvider;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            Contracts\ReportingInterface::class,
            Services\ReportingService::class
        );
        $this->loadViewsFrom(__DIR__.'/Resources/Views', 'reporting');
    }

    public function boot(): void
    {
        //
    }
}
