<?php

namespace App\Services\DailyReport;

use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class WidgetDataService
{
    /**
     * Build unit summaries for a given laporan and current user
     * Returns array of summaries matching previous widget shape
     */
    public function getUnitSummariesForLaporan(LaporanImut $laporan): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $start = $laporan->assessment_period_start;
        $end = $laporan->assessment_period_end;

        $units = $user->unitKerjas()
            ->whereHas('laporanUnitKerjas', function ($q) use ($laporan) {
                $q->where('laporan_imut_id', $laporan->id);
            })
            ->get();

        $summaries = [];

        $repo = app(DailyReportResponseRepositoryInterface::class);

        foreach ($units as $unit) {
            // gather relevant formTemplate ids for this unit within period
            $formIds = $unit->imutData()
                ->where('status', true)
                ->with(['profiles' => function ($q) use ($start, $end) {
                    $q->validForPeriod($start, $end);
                }, 'profiles.formTemplates'])
                ->get()
                ->flatMap(function ($imutData) {
                    return $imutData->profiles->flatMap->formTemplates;
                })
                ->pluck('id')
                ->unique()
                ->toArray();

            // basic counts
            $today = now()->toDateString();

            $todayCount = $repo->countByDateForUnitAndFormIds($unit->id, $today, $formIds);

            $perfectCount = $repo->countPerfectBetweenForUnitAndFormIds($unit->id, $start, $end, $formIds);

            $last = $repo->getLatestForUnitAndFormIds($unit->id, $start, $end, $formIds);

            // advanced stats
            $totalIndicators = count($formIds);
            $daysPassed = min($start->diffInDays(now()) + 1, $start->diffInDays($end) + 1);
            $expectedReports = $totalIndicators * $daysPassed;
            $actualReports = $repo->countBetweenForUnitAndFormIds($unit->id, $start, min(now(), $end), $formIds);

            $completionRate = $expectedReports > 0
                ? round(($actualReports / $expectedReports) * 100, 1)
                : 0;

            $complianceRate = $actualReports > 0
                ? round(($perfectCount / $actualReports) * 100, 1)
                : 0;

            // daily trend last 7 days
            $trend = [];
            $base = now()->subDays(6);
            for ($i = 0; $i < 7; $i++) {
                $d = $base->copy()->addDays($i)->toDateString();
                $trend[$d] = $repo->countByDateForUnitAndFormIds($unit->id, $d, $formIds);
            }

            // recent reports list
            $recent = $repo->getRecentForUnitAndFormIds($unit->id, $start, $end, $formIds, 5);

            $summaries[] = [
                'unit_id' => $unit->id,
                'unit_name' => $unit->unit_name,
                'today' => $todayCount,
                'perfect' => $perfectCount,
                'last_submission' => $last?->created_at,
                'total_indicators' => $totalIndicators,
                'expected_reports' => $expectedReports,
                'actual_reports' => $actualReports,
                'completion_rate' => $completionRate,
                'compliance_rate' => $complianceRate,
                'trend' => $trend,
                'recent_reports' => $recent,
            ];
        }

        return $summaries;
    }

    /**
     * Calculate category achievement statistics for a given laporan
     */
    public function getImutCapaianStats(LaporanImut $laporan, array $categories): array
    {
        if (! $laporan) {
            return [
                'total_categories' => count($categories),
                'categories_detail' => [],
                'total_imut_indicators' => 0,
                'imut_meeting_standard' => 0,
                'imut_below_standard' => 0,
                'overall_achievement' => 0,
                'available_laporans' => [],
                'selected_laporan_id' => $laporan?->id ?? null,
            ];
        }

        $selectedLaporanCollection = collect([$laporan]);

        $availableLaporans = [$laporan->id => $laporan->name];

        $stats = [
            'total_categories' => count($categories),
            'categories_detail' => [],
            'total_imut_indicators' => 0,
            'imut_meeting_standard' => 0,
            'imut_below_standard' => 0,
            'overall_achievement' => 0,
            'laporan_used' => $laporan->name,
            'laporan_period' => $laporan->assessment_period_start?->format('F Y'),
            'available_laporans' => array_map(function ($l) { return ['id' => $l->id, 'name' => $l->name, 'period' => $l->assessment_period_start->format('F Y')]; }, $selectedLaporanCollection->toArray()),
            'selected_laporan_id' => $laporan->id,
        ];

        foreach ($categories as $categoryShortName) {
            $categoryStats = [
                'category_id' => $categoryShortName,
                'category_name' => $categoryShortName,
                'total_imut' => 0,
                'imut_meeting_standard' => 0,
                'imut_below_standard' => 0,
                'achievement_percentage' => 0,
            ];

            $imutDataMap = [];

            foreach ($selectedLaporanCollection as $laporanItem) {
                foreach ($laporanItem->laporanUnitKerjas as $laporanUnitKerja) {
                    foreach ($laporanUnitKerja->imutPenilaians as $penilaian) {
                        $imutData = $penilaian->profile->imutData ?? null;
                        if (! $imutData) continue;

                        $imutCategory = $imutData->categories;
                        if (! $imutCategory || $imutCategory->short_name != $categoryShortName) {
                            continue;
                        }

                        $imutId = $imutData->id;

                        if (! isset($imutDataMap[$imutId])) {
                            $imutDataMap[$imutId] = [
                                'title' => $imutData->title,
                                'standard' => $penilaian->profile->target_value ?? 0,
                                'operator' => $penilaian->profile->target_operator ?? '>=',
                                'total_numerator' => 0,
                                'total_denominator' => 0,
                            ];
                        }

                        if ($penilaian->numerator_value !== null && $penilaian->denominator_value !== null && $penilaian->denominator_value != 0) {
                            $imutDataMap[$imutId]['total_numerator'] += $penilaian->numerator_value;
                            $imutDataMap[$imutId]['total_denominator'] += $penilaian->denominator_value;
                        }
                    }
                }
            }

            // compute per-category aggregates
            foreach ($imutDataMap as $imutId => $data) {
                $total = $data['total_denominator'] ?: 0;
                $numerator = $data['total_numerator'] ?: 0;

                $categoryStats['total_imut']++;
                if ($total > 0) {
                    $percentage = ($numerator / $total) * 100;
                    if ($percentage >= ($data['standard'] ?? 0)) {
                        $categoryStats['imut_meeting_standard']++;
                    } else {
                        $categoryStats['imut_below_standard']++;
                    }
                }
            }

            $categoryStats['achievement_percentage'] = $categoryStats['total_imut'] > 0
                ? round(($categoryStats['imut_meeting_standard'] / $categoryStats['total_imut']) * 100, 1)
                : 0;

            $stats['categories_detail'][] = $categoryStats;
        }

        // compute summary totals
        $stats['total_imut_indicators'] = array_sum(array_column($stats['categories_detail'], 'total_imut'));
        $stats['imut_meeting_standard'] = array_sum(array_column($stats['categories_detail'], 'imut_meeting_standard'));
        $stats['imut_below_standard'] = array_sum(array_column($stats['categories_detail'], 'imut_below_standard'));
        $stats['overall_achievement'] = $stats['total_imut_indicators'] > 0
            ? round(($stats['imut_meeting_standard'] / $stats['total_imut_indicators']) * 100, 1)
            : 0;

        return $stats;
    }
}
