<?php

namespace App\Services\DailyReport\Monitoring;

if (!class_exists(\App\Services\DailyReport\Monitoring\MonitoringTemplateService::class, false)) {
    class_alias(\App\Modules\DailyReport\Services\Monitoring\MonitoringTemplateService::class, \App\Services\DailyReport\Monitoring\MonitoringTemplateService::class);
}
