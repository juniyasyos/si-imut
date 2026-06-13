<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\DailyReportMonitoringService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\DailyReportMonitoringService::class, \App\Services\DailyReport\DailyReportMonitoringService::class);
}
