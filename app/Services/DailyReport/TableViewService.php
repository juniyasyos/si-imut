<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\TableViewService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\TableViewService::class, \App\Services\DailyReport\TableViewService::class);
}
