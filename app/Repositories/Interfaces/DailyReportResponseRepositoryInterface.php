<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface DailyReportResponseRepositoryInterface
{
    public function getReportsForIndicatorDate(
        int $indicatorId,
        string $date,
        array $unitKerjaIds
    ): Collection;

    public function countReportsForIndicatorDate(
        int $indicatorId,
        string $date,
        array $unitKerjaIds
    ): int;

    public function getFieldResponsesForReportIds(array $reportIds): Collection;

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

    // Additional helpers for multi-form queries used by widgets/services
    public function countByDateForUnitAndFormIds(int $unitKerjaId, string $date, array $formTemplateIds): int;

    public function countBetweenForUnitAndFormIds(int $unitKerjaId, Carbon $startDate, Carbon $endDate, array $formTemplateIds): int;

    public function countPerfectBetweenForUnitAndFormIds(int $unitKerjaId, Carbon $startDate, Carbon $endDate, array $formTemplateIds): int;

    public function getLatestForUnitAndFormIds(int $unitKerjaId, Carbon $startDate, Carbon $endDate, array $formTemplateIds);

    public function getRecentForUnitAndFormIds(int $unitKerjaId, Carbon $startDate, Carbon $endDate, array $formTemplateIds, int $limit = 5);

    /**
     * Return an Eloquent query builder pre-filtered for an IMUT profile's reports.
     * Useful for UI tables that need a Builder.
     */
    public function getQueryForProfile(int $imutProfileId);

    // Basic CRUD helpers
    public function findById(int $id);

    public function getByIdWithRelations(int $id, array $relations = []);

    public function createReport(array $data);

    public function updateById(int $id, array $data): bool;

    public function deleteById(int $id): bool;
}