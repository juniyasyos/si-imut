<?php

namespace App\Models;

if (!class_exists(\App\Models\DailyReportResponse::class, false)) {
    class_alias(\App\Modules\DailyReport\Models\DailyReportResponse::class, \App\Models\DailyReportResponse::class);
}
