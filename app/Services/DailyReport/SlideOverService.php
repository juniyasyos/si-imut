<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\SlideOverService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\SlideOverService::class, \App\Services\DailyReport\SlideOverService::class);
}
