<?php

namespace App\Repositories;

use App\Models\DailyReportResponse;
use App\Models\User;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DailyReportResponseRepository implements DailyReportResponseRepositoryInterface
{
    public function getTableViewEntries(
        User $user,
        ?int $formTemplateId,
        ?int $unitKerjaId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $query = DailyReportResponse::query()
            ->with([
                'formTemplate.formFields.options',
                'formTemplate.imutProfile.imutData',
                'unitKerja',
                'submittedBy',
                'validator',
                'fieldResponses.formField.options',
            ])
            ->whereBetween('report_date', [$startDate, $endDate]);

        if ($formTemplateId) {
            $query->where('form_template_id', $formTemplateId);
        } else {
            $query->whereHas('formTemplate', fn ($q) => $q->where('is_active', true));
        }

        if ($unitKerjaId) {
            $query->where('unit_kerja_id', $unitKerjaId);
        } else {
            $query->forUserUnits($user);
        }

        return $query->orderBy('report_date', 'asc')->get();
    }

    public function countReportedEntries(
        int $unitKerjaId,
        int $formTemplateId,
        Carbon $startDate,
        Carbon $endDate
    ): int {
        return DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->where('form_template_id', $formTemplateId)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->count();
    }

    public function countPerfectEntries(
        int $unitKerjaId,
        int $formTemplateId,
        Carbon $startDate,
        Carbon $endDate
    ): int {
        return DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->where('form_template_id', $formTemplateId)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->where(function ($q) {
                $q->where('compliance_status', true)
                    ->orWhereRaw("JSON_EXTRACT(calculation_details, '$.compliance_status') = true")
                    ->orWhere('total_score', '>=', 100)
                    ->orWhereRaw("JSON_EXTRACT(calculation_details, '$.total_score') >= 100");
            })
            ->count();
    }

    public function getMissingReportDates(
        int $unitKerjaId,
        int $formTemplateId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $reportedDates = DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->where('form_template_id', $formTemplateId)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(report_date) as report_date'))
            ->distinct()
            ->pluck('report_date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        $missingDates = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateString = $current->format('Y-m-d');
            if (! in_array($dateString, $reportedDates, true)) {
                $missingDates[] = $dateString;
            }
            $current->addDay();
        }

        return $missingDates;
    }
}