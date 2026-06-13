<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\DailyReportAuthorizationService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\DailyReportAuthorizationService::class, \App\Services\DailyReport\DailyReportAuthorizationService::class);
}
