<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\WidgetDataService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\WidgetDataService::class, \App\Services\DailyReport\WidgetDataService::class);
}
