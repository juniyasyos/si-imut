<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\CachedSettingsService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\CachedSettingsService::class, \App\Services\DailyReport\CachedSettingsService::class);
}
