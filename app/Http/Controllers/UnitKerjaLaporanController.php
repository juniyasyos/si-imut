<?php

namespace App\Http\Controllers;

use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Support\PeriodFilter;
use Illuminate\Http\Request;

class UnitKerjaLaporanController extends Controller
{
    /**
     * Show unit kerja report with all IMUT data
     * 
     * URL: /laporan/indikator-mutu/unit-kerja/{unitKerja}/{tipe}/{periode}
     * Example: /laporan/indikator-mutu/unit-kerja/administrasi/yearly/2026
     */
    public function show(Request $request, $unitKerja, $tipe = 'yearly', $periode = null)
    {
        // Find unit kerja
        $unit = UnitKerja::where('slug', $unitKerja)
            ->orWhere('id', $unitKerja)
            ->firstOrFail();

        // Default periode to current year if not provided
        if (!$periode) {
            $periode = now()->year;
        }

        // Get date range from period filter
        try {
            $dateRange = PeriodFilter::getDateRange($tipe, $periode);
        } catch (\Exception $e) {
            abort(400, 'Invalid period format');
        }

        // Get only active IMUT data for this unit
        // `status` field on imut_data indicates whether the indicator is active.
        // join through the pivot relation then filter by imut_data.status = true.
        $imutDataItems = $unit->imutData()
            ->where('status', true)
            ->get();

        // Get laporan entries within date range that cover this unit
        $laporanList = LaporanImut::whereBetween('assessment_period_start', [
            $dateRange['start'],
            $dateRange['end'],
        ])->orWhereBetween('assessment_period_end', [
            $dateRange['start'],
            $dateRange['end'],
        ])->orWhere(function ($q) use ($dateRange) {
            $q->where('assessment_period_start', '<=', $dateRange['start'])
                ->where('assessment_period_end', '>=', $dateRange['end']);
        })->orderBy('assessment_period_start')->get();

        // Build data for each IMUT
        $dataByImut = [];
        $allMonths = PeriodFilter::getMonthsInRange($dateRange['start'], $dateRange['end']);

        foreach ($imutDataItems as $imutData) {
            // Get the active profile for this IMUT data
            // First try to find profile valid for the entire period
            $profile = $imutData->profiles()
                ->validForPeriod($dateRange['start'], $dateRange['end'])
                ->orderBy('valid_from', 'desc')
                ->first();

            // If no profile is valid for the entire period, get the most recent profile
            // that was valid before or at the start of the period
            if (!$profile) {
                $profile = $imutData->profiles()
                    ->where(function ($q) use ($dateRange) {
                        $q->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', $dateRange['start']->toDateString());
                    })
                    ->where(function ($q) use ($dateRange) {
                        $q->whereNull('valid_from')
                            ->orWhere('valid_from', '<=', $dateRange['start']->toDateString());
                    })
                    ->orderBy('valid_from', 'desc')
                    ->first();
            }

            // If still no profile found, use the most recent profile available
            if (!$profile) {
                $profile = $imutData->profiles()
                    ->orderBy('valid_from', 'desc')
                    ->first();
            }

            $targetValue = $profile?->target_value ?? 0;
            $targetOperator = $profile->target_operator;

            $imutDetail = [
                'id' => $imutData->id,
                'title' => $imutData->title,
                'category' => $imutData->categories?->category_name ?? 'Uncategorized',
                'standard' => $targetValue,
                'target_operator' => $targetOperator,
                'data' => [],
            ];

            // Get data points for each month
            foreach ($allMonths as $month) {
                $laporan = $laporanList->first(function ($l) use ($month) {
                    return $l->assessment_period_start->format('Y-m') <= $month['value']
                        && $l->assessment_period_end->format('Y-m') >= $month['value'];
                });

                if ($laporan) {
                    // Get assessment data for this unit and imut data
                    // ImutPenilaian -> laporanUnitKerja -> unit_kerja
                    // ImutPenilaian -> profile -> imutData
                    $penilaian = $laporan->imutPenilaians()
                        ->whereHas('laporanUnitKerja', fn($q) => $q->where('unit_kerja_id', $unit->id))
                        ->whereHas('profile.imutData', fn($q) => $q->where('imut_data_id', $imutData->id))
                        ->first();

                    if ($penilaian) {
                        $percentage = $penilaian->denominator_value > 0
                            ? ($penilaian->numerator_value / $penilaian->denominator_value) * 100
                            : 0;

                        // Determine status based on target operator
                        $status = $this->checkTargetStatus($percentage, $targetValue, $targetOperator);

                        $imutDetail['data'][] = [
                            'month' => $month['value'],
                            'month_label' => $month['label'],
                            'numerator' => $penilaian->numerator_value ?? 0,
                            'denominator' => $penilaian->denominator_value ?? 0,
                            'percentage' => round($percentage, 2),
                            'status' => $status,
                            'analysis' => $penilaian->analysis ?? '',
                            'recommendations' => $penilaian->recommendations ?? '',
                        ];
                    } else {
                        $imutDetail['data'][] = [
                            'month' => $month['value'],
                            'month_label' => $month['label'],
                            'numerator' => 0,
                            'denominator' => 0,
                            'percentage' => 0,
                            'status' => 'no-data',
                            'analysis' => '',
                            'recommendations' => '',
                        ];
                    }
                } else {
                    $imutDetail['data'][] = [
                        'month' => $month['value'],
                        'month_label' => $month['label'],
                        'numerator' => 0,
                        'denominator' => 0,
                        'percentage' => 0,
                        'status' => 'no-data',
                        'analysis' => '',
                        'recommendations' => '',
                    ];
                }
            }

            $dataByImut[] = $imutDetail;
        }

        // Calculate summary
        $summary = $this->calculateSummary($dataByImut);

        // Prepare period label
        $periodLabel = PeriodFilter::formatPeriodLabel($tipe, $periode);

        // Build chart data
        $chartData = $this->buildChartData($dataByImut);

        // Prepare signatories for footer using SignatoryService
        $signatoryService = new \App\Services\SignatoryService();
        $signatories = $signatoryService->pickForUnit($unit);

        // Format users for blade component (keep compatibility with existing props)
        $formatForView = function ($user) use ($signatories, $signatoryService) {
            if (! $user) return null;

            // Use SignatoryService to resolve TTD (S3 first, then public/local)
            $ttd = $signatoryService->getTtdUrl($user);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'ttd_url' => $ttd ? trim($ttd) : '',
            ];
        };

        $usersByUnit = [
            'pengumpul_data' => $signatories['pengumpul'] ? [$formatForView($signatories['pengumpul'])] : [],
            'validator' => $signatories['validator'] ? [$formatForView($signatories['validator'])] : [],
            'unit_kerja_id' => $unit->id,
            'unit_kerja_name' => $unit->unit_name,
        ];

        return view('reports.unit-kerja-laporan', [
            'unit' => $unit,
            'tipe' => $tipe,
            'periode' => $periode,
            'periodLabel' => $periodLabel,
            'dateRange' => $dateRange,
            'dataByImut' => $dataByImut,
            'summary' => $summary,
            'allMonths' => $allMonths,
            'chartData' => $chartData,
            'usersByUnit' => $usersByUnit,
        ]);
    }

    /**
     * Build chart data structure for JavaScript
     */
    private function buildChartData(array $dataByImut): array
    {
        $chartDataMap = [];

        foreach ($dataByImut as $imut) {
            $labels = [];
            $percentages = [];

            foreach ($imut['data'] as $dataPoint) {
                $labels[] = $dataPoint['month_label'];
                $percentages[] = $dataPoint['percentage'];
            }

            $chartDataMap['chart-' . $imut['id']] = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Pencapaian (%)',
                        'data' => $percentages,
                        'borderColor' => '#0284c7',
                        'backgroundColor' => 'rgba(2, 132, 199, 0.1)',
                        'borderWidth' => 2,
                        'pointBackgroundColor' => '#0284c7',
                        'pointBorderColor' => '#fff',
                        'pointBorderWidth' => 2,
                        'pointRadius' => 4,
                        'pointHoverRadius' => 6,
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Target Standar',
                        'data' => array_fill(0, count($imut['data']), $imut['standard']),
                        'borderColor' => '#ef4444',
                        'borderDash' => [5, 5],
                        'borderWidth' => 2,
                        'pointRadius' => 0,
                        'fill' => false,
                    ]
                ]
            ];
        }

        return $chartDataMap;
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary(array $dataByImut): array
    {
        $totalImutData = count($dataByImut);
        $totalDataPoints = 0;
        $achievedCount = 0;
        $totalPercentage = 0;
        $totalNumerator = 0;
        $totalDenominator = 0;

        foreach ($dataByImut as $imut) {
            foreach ($imut['data'] as $point) {
                if ($point['status'] !== 'no-data') {
                    $totalDataPoints++;
                    $totalPercentage += $point['percentage'];
                    $totalNumerator += $point['numerator'];
                    $totalDenominator += $point['denominator'];

                    if ($point['status'] === 'achieved') {
                        $achievedCount++;
                    }
                }
            }
        }

        $averagePercentage = $totalDataPoints > 0 ? $totalPercentage / $totalDataPoints : 0;

        return [
            'total_imut_data' => $totalImutData,
            'total_data_points' => $totalDataPoints,
            'achieved_count' => $achievedCount,
            'average_percentage' => round($averagePercentage, 2),
            'total_numerator' => $totalNumerator,
            'total_denominator' => $totalDenominator,
            'overall_percentage' => $totalDenominator > 0
                ? round(($totalNumerator / $totalDenominator) * 100, 2)
                : 0,
        ];
    }

    /**
     * Check if a value meets the target threshold based on operator
     */
    private function checkTargetStatus($value, $target, $operator): string
    {
        if ($target === 0 || $target === null) {
            return 'no-data';
        }

        $achieved = false;

        switch ($operator) {
            case '>=':
            case '≥':
                $achieved = $value >= $target;
                break;
            case '>':
                $achieved = $value > $target;
                break;
            case '<=':
            case '≤':
                $achieved = $value <= $target;
                break;
            case '<':
                $achieved = $value < $target;
                break;
            case '=':
            case '==':
                $achieved = $value == $target;
                break;
            default:
                $achieved = $value >= $target;
        }

        return $achieved ? 'achieved' : 'not-achieved';
    }
}
