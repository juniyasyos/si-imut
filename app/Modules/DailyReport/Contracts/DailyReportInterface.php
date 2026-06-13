<?php

namespace App\Modules\DailyReport\Contracts;

use App\Modules\DailyReport\Models\DailyReportResponse;
use App\Models\User;

interface DailyReportInterface
{
    /**
     * Create Daily Report with authorization checks and single-pass scoring
     */
    public function createWithAuthorization(
        User $user,
        int $templateId,
        string $reportDate,
        array $formData,
        int $unitKerjaId
    ): DailyReportResponse;
}
