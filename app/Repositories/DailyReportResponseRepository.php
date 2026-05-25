<?php

namespace App\Repositories;

use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\User;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DailyReportResponseRepository implements DailyReportResponseRepositoryInterface
{
    public function getReportsForIndicatorDate(
        int $indicatorId,
        string $date,
        array $unitKerjaIds
    ): Collection {
        $query = DailyReportResponse::query()
            ->select([
                'daily_report_responses.*',
                'unit_kerja.unit_name as unit_name',
                'users.name as submitted_by_name',
                'form_templates.title as form_title',
            ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('unit_kerja', 'daily_report_responses.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('users', 'daily_report_responses.submitted_by', '=', 'users.id')
            ->where('form_templates.id', $indicatorId)
            ->where(function ($query) {
                $now = now();
                $query->where(function ($nestedQuery) use ($now) {
                    $nestedQuery->where('imut_profil.valid_from', '<=', $now)
                        ->where(function ($validQuery) use ($now) {
                            $validQuery->whereNull('imut_profil.valid_until')
                                ->orWhere('imut_profil.valid_until', '>=', $now);
                        });
                });
            })
            ->whereDate('daily_report_responses.report_date', $date)
            ->latest('daily_report_responses.created_at');

        if (! empty($unitKerjaIds)) {
            $query->whereIn('daily_report_responses.unit_kerja_id', $unitKerjaIds);
        }

        return $query->get();
    }

    public function countReportsForIndicatorDate(
        int $indicatorId,
        string $date,
        array $unitKerjaIds
    ): int {
        $query = DailyReportResponse::query()
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->where('form_templates.id', $indicatorId)
            ->whereDate('daily_report_responses.report_date', $date);

        if (! empty($unitKerjaIds)) {
            $query->whereIn('daily_report_responses.unit_kerja_id', $unitKerjaIds);
        }

        return $query
            ->where(function ($query) {
                $now = now();
                $query->where(function ($nestedQuery) use ($now) {
                    $nestedQuery->where('imut_profil.valid_from', '<=', $now)
                        ->where(function ($validQuery) use ($now) {
                            $validQuery->whereNull('imut_profil.valid_until')
                                ->orWhere('imut_profil.valid_until', '>=', $now);
                        });
                });
            })
            ->count();
    }

    public function getFieldResponsesForReportIds(array $reportIds): Collection
    {
        if (empty($reportIds)) {
            return collect();
        }

        return FieldResponse::query()
            ->select([
                'field_responses.*',
                'enhanced_form_fields.field_label',
            ])
            ->join('enhanced_form_fields', 'field_responses.form_field_id', '=', 'enhanced_form_fields.id')
            ->whereIn('field_responses.daily_report_response_id', $reportIds)
            ->get()
            ->groupBy('daily_report_response_id');
    }

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

    public function countByDateForUnitAndFormIds(int $unitKerjaId, string $date, array $formTemplateIds): int
    {
        $query = DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->whereDate('report_date', $date);

        if (! empty($formTemplateIds)) {
            $query->whereIn('form_template_id', $formTemplateIds);
        }

        return $query->count();
    }

    public function countBetweenForUnitAndFormIds(int $unitKerjaId, Carbon $startDate, Carbon $endDate, array $formTemplateIds): int
    {
        $query = DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->whereBetween('report_date', [$startDate, $endDate]);

        if (! empty($formTemplateIds)) {
            $query->whereIn('form_template_id', $formTemplateIds);
        }

        return $query->count();
    }

    public function countPerfectBetweenForUnitAndFormIds(int $unitKerjaId, Carbon $startDate, Carbon $endDate, array $formTemplateIds): int
    {
        $query = DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->where(function ($q) {
                $q->where('compliance_status', true)
                    ->orWhereRaw("JSON_EXTRACT(calculation_details, '$.compliance_status') = true")
                    ->orWhere('total_score', '>=', 100)
                    ->orWhereRaw("JSON_EXTRACT(calculation_details, '$.total_score') >= 100");
            });

        if (! empty($formTemplateIds)) {
            $query->whereIn('form_template_id', $formTemplateIds);
        }

        return $query->count();
    }

    public function getLatestForUnitAndFormIds(int $unitKerjaId, Carbon $startDate, Carbon $endDate, array $formTemplateIds)
    {
        $query = DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->whereBetween('report_date', [$startDate, $endDate]);

        if (! empty($formTemplateIds)) {
            $query->whereIn('form_template_id', $formTemplateIds);
        }

        return $query->latest('created_at')->first();
    }

    public function getRecentForUnitAndFormIds(int $unitKerjaId, Carbon $startDate, Carbon $endDate, array $formTemplateIds, int $limit = 5)
    {
        $query = DailyReportResponse::with(['formTemplate'])
            ->where('unit_kerja_id', $unitKerjaId)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->latest('created_at')
            ->limit($limit);

        if (! empty($formTemplateIds)) {
            $query->whereIn('form_template_id', $formTemplateIds);
        }

        return $query->get();
    }

    public function getQueryForProfile(int $imutProfileId)
    {
        return DailyReportResponse::query()
            ->whereHas('formTemplate', function ($q) use ($imutProfileId) {
                $q->where('imut_profile_id', $imutProfileId);
            })
            ->with(['unitKerja', 'submittedBy', 'formTemplate']);
    }

    public function findById(int $id)
    {
        return DailyReportResponse::find($id);
    }

    public function getByIdWithRelations(int $id, array $relations = [])
    {
        $query = DailyReportResponse::query();
        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    public function createReport(array $data)
    {
        return DailyReportResponse::create($data);
    }

    public function updateById(int $id, array $data): bool
    {
        $model = DailyReportResponse::find($id);
        if (! $model) return false;
        return $model->update($data);
    }

    public function deleteById(int $id): bool
    {
        $model = DailyReportResponse::find($id);
        if (! $model) return false;
        return (bool) $model->delete();
    }
}