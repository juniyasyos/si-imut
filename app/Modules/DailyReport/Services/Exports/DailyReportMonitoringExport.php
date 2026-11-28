<?php

namespace App\Modules\DailyReport\Services\Exports;

use App\Exports\MonitoringExport;
use App\Modules\FormEngine\Models\FormTemplate;

/**
 * Export class for Daily Report Monitoring data to Excel
 */
class DailyReportMonitoringExport extends MonitoringExport
{
    public function __construct(FormTemplate $template)
    {
        parent::__construct($template);
    }
}
