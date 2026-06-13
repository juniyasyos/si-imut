<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\DailyReportEntryContextService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\DailyReportEntryContextService::class, \App\Services\DailyReport\DailyReportEntryContextService::class);
}