<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\UnifiedComplianceService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\UnifiedComplianceService::class, \App\Services\DailyReport\UnifiedComplianceService::class);
}