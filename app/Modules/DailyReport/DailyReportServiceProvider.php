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
    }

    public function boot(): void
    {
        //
    }
}
