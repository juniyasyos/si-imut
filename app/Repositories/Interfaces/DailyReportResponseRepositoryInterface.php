<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface DailyReportResponseRepositoryInterface
{
    public function getTableViewEntries(
        User $user,
        ?int $formTemplateId,
        ?int $unitKerjaId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection;

    public function countReportedEntries(
        int $unitKerjaId,
        int $formTemplateId,
        Carbon $startDate,
        Carbon $endDate
    ): int;

    public function countPerfectEntries(
        int $unitKerjaId,
        int $formTemplateId,
        Carbon $startDate,
        Carbon $endDate
    ): int;

    public function getMissingReportDates(
        int $unitKerjaId,
        int $formTemplateId,
        Carbon $startDate,
        Carbon $endDate
    ): array;
}