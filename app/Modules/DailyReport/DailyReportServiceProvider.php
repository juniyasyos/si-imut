<?php

namespace App\Modules\DailyReport;

use Illuminate\Support\ServiceProvider;

class DailyReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            Contracts\DailyReportInterface::class,
            Services\DailyReportService::class
        );
        $this->loadViewsFrom(__DIR__.'/Resources/Views', 'daily-report');
    }

    public function boot(): void
    {
        //
    }
}
