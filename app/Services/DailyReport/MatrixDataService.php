<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\MatrixDataService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\MatrixDataService::class, \App\Services\DailyReport\MatrixDataService::class);
}
