<?php

namespace App\Models;

if (!class_exists(\App\Models\DailyReportEntry::class, false)) {
    class_alias(\App\Modules\DailyReport\Models\DailyReportEntry::class, \App\Models\DailyReportEntry::class);
}
