<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\DailyReportBuildService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\DailyReportBuildService::class, \App\Services\DailyReport\DailyReportBuildService::class);
}
