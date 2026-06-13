<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\DailyReportService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\DailyReportService::class, \App\Services\DailyReport\DailyReportService::class);
}
