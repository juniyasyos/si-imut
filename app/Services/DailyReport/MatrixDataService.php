<?php

namespace App\Services\DailyReport;

use App\Models\FormTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatrixDataService
{
    /**
     * Load matrix data for selected month
     */
    public function loadMatrixData(string $selectedMonth): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['indicators' => [], 'matrixData' => [], 'daysInMonth' => []];
        }

        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        if (empty($unitKerjaIds)) {
            return ['indicators' => [], 'matrixData' => [], 'daysInMonth' => []];
        }

        // Get indicators
        $indicators = $this->getIndicators($unitKerjaIds);

        // Calculate days in selected month
        $date = Carbon::parse($selectedMonth . '-01');
        $daysInMonth = range(1, $date->daysInMonth);

        // Get compliance summaries
        $complianceSummaries = $this->getComplianceSummaries($unitKerjaIds, $date);

        // Build matrix data
        $matrixData = $this->buildMatrixData($indicators, $daysInMonth, $date, $complianceSummaries);

        return [
            'indicators' => $indicators,
            'matrixData' => $matrixData,
            'daysInMonth' => $daysInMonth
        ];
    }

    /**
     * Get indicators for user's units
     */
    private function getIndicators(array $unitKerjaIds): array
    {
        $formTemplates = FormTemplate::select([
            'form_templates.id',
            'form_templates.title',
            'imut_data.title as imut_data_title',
            'imut_kategori.category_name as category_title',
            'imut_profil.version as imut_profile_version',
        ])
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->leftJoin('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->whereIn('imut_data_unit_kerja.unit_kerja_id', $unitKerjaIds)
            ->where('imut_data.is_monthly', true) // only monthly indicators
            ->where(function ($query) {
                $now = now();
                $query->where(function ($q) use ($now) {
                    $q->where('imut_profil.valid_from', '<=', $now)
                        ->where(function ($subQ) use ($now) {
                            $subQ->whereNull('imut_profil.valid_until')
                                ->orWhere('imut_profil.valid_until', '>=', $now);
                        });
                });
            })
            ->distinct()
            ->get();

        return $formTemplates->map(function ($formTemplate) {
            return [
                'id' => $formTemplate->id,
                'title' => $formTemplate->imut_data_title ?? $formTemplate->title,
                'category' => $formTemplate->category_title,
                'imut_profile_version' => $formTemplate->imut_profile_version,
            ];
        })->toArray();
    }

    /**
     * Get compliance summaries for the month
     */
    private function getComplianceSummaries(array $unitKerjaIds, Carbon $date): \Illuminate\Support\Collection
    {
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');

        return \App\Models\DailyReportResponse::select([
            'form_templates.id as form_template_id',
            DB::raw('DATE(daily_report_responses.report_date) as report_date'),
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(CASE WHEN compliance_status = 1 THEN 1 ELSE 0 END) as compliant_count')
        ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->whereIn('imut_data_unit_kerja.unit_kerja_id', $unitKerjaIds)
            ->where(function ($query) {
                $now = now();
                $query->where(function ($q) use ($now) {
                    $q->where('imut_profil.valid_from', '<=', $now)
                        ->where(function ($subQ) use ($now) {
                            $subQ->whereNull('imut_profil.valid_until')
                                ->orWhere('imut_profil.valid_until', '>=', $now);
                        });
                });
            })
            ->whereBetween('daily_report_responses.report_date', [$startDate, $endDate])
            ->groupBy('form_templates.id', DB::raw('DATE(daily_report_responses.report_date)'))
            ->get()
            ->groupBy('form_template_id')
            ->map(function ($dates) {
                return $dates->keyBy('report_date');
            });
    }

    /**
     * Build matrix data array
     */
    private function buildMatrixData(array $indicators, array $daysInMonth, Carbon $date, $complianceSummaries): array
    {
        $matrixData = [];

        foreach ($indicators as $indicator) {
            foreach ($daysInMonth as $day) {
                $dateStr = $date->copy()->day($day)->format('Y-m-d');
                $summary = $complianceSummaries->get($indicator['id'])?->get($dateStr);

                $totalCount = $summary ? $summary->total_count : 0;
                $compliantCount = $summary ? $summary->compliant_count : 0;
                $compliancePercentage = $totalCount > 0 ? round(($compliantCount / $totalCount) * 100, 1) : 0;

                $cellDate = $date->copy()->day($day)->startOfDay();
                $today = now()->startOfDay();
                $sixDaysAgo = now()->copy()->subDays(6)->startOfDay();

                $cellState = 'disabled';
                if ($cellDate->lte($today)) {
                    if ($totalCount > 0) {
                        $cellState = 'done';
                    } elseif ($cellDate->gte($sixDaysAgo)) {
                        $cellState = 'pending';
                    } else {
                        $cellState = 'overdue';
                    }
                }

                $summaryData = null;
                if ($totalCount > 0) {
                    $summaryData = [
                        'count' => $totalCount,
                        'numerator' => $compliantCount,
                        'denominator' => $totalCount,
                        'percentage' => $compliancePercentage,
                    ];
                }

                $matrixData[$indicator['id']][$day] = [
                    'date' => $dateStr,
                    'has_data' => $totalCount > 0,
                    'count' => $totalCount,
                    'compliance_percentage' => $compliancePercentage,
                    'compliance_count' => $compliantCount,
                    'total_count' => $totalCount,
                    'cell_state' => $cellState,
                    'summary' => $summaryData,
                    'is_today' => $cellDate->isToday(),
                ];
            }
        }

        return $matrixData;
    }

    /**
     * Get real indicator status from database
     */
    public function getRealIndicatorStatus(int $indicatorId, string $date): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['error' => 'User not authenticated', 'status' => 'error', 'count' => 0];
        }

        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        if (empty($unitKerjaIds)) {
            return ['error' => 'No unit kerja found', 'status' => 'error', 'count' => 0];
        }

        $reports = \App\Models\DailyReportResponse::select([
            'daily_report_responses.id',
            'daily_report_responses.report_date',
            'daily_report_responses.compliance_status',
            'daily_report_responses.total_score',
            'daily_report_responses.created_at'
        ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->where('form_templates.id', $indicatorId)
            ->whereIn('daily_report_responses.unit_kerja_id', $unitKerjaIds)
            ->whereDate('daily_report_responses.report_date', $date)
            ->get();

        $count = $reports->count();
        $cellDate = Carbon::parse($date)->startOfDay();
        $today = now()->startOfDay();
        $sixDaysAgo = now()->copy()->subDays(6)->startOfDay();

        if ($count > 0) {
            $status = 'done';
        } elseif ($cellDate->lte($today) && $cellDate->gte($sixDaysAgo)) {
            $status = 'pending';
        } elseif ($cellDate->lt($sixDaysAgo)) {
            $status = 'overdue';
        } else {
            $status = 'disabled';
        }

        return [
            'status' => $status,
            'count' => $count,
            'reports' => $reports->toArray(),
            'date' => $date
        ];
    }
}
