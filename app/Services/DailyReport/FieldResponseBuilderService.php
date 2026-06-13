<?php

namespace App\Services\DailyReport;

if (!class_exists(\App\Services\DailyReport\FieldResponseBuilderService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\FieldResponseBuilderService::class, \App\Services\DailyReport\FieldResponseBuilderService::class);
}
