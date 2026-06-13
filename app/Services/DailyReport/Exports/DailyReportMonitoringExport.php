<?php

namespace App\Services\DailyReport\Exports;

if (!class_exists(\App\Services\DailyReport\Exports\DailyReportMonitoringExport::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\Exports\DailyReportMonitoringExport::class, \App\Services\DailyReport\Exports\DailyReportMonitoringExport::class);
}
