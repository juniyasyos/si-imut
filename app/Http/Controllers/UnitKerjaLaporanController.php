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

        // Get all IMUT data for this unit
        $imutDataItems = $unit->imutData()->get();

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
            $imutDetail = [
                'id' => $imutData->id,
                'title' => $imutData->title,
                'category' => $imutData->kategori?->name ?? 'Uncategorized',
                'standard' => $imutData->imut_standard ?? 0,
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

                        $imutDetail['data'][] = [
                            'month' => $month['value'],
                            'month_label' => $month['label'],
                            'numerator' => $penilaian->numerator_value ?? 0,
                            'denominator' => $penilaian->denominator_value ?? 0,
                            'percentage' => round($percentage, 2),
                            'status' => $percentage >= $imutData->imut_standard ? 'achieved' : 'not-achieved',
                        ];
                    } else {
                        $imutDetail['data'][] = [
                            'month' => $month['value'],
                            'month_label' => $month['label'],
                            'numerator' => 0,
                            'denominator' => 0,
                            'percentage' => 0,
                            'status' => 'no-data',
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
                    ];
                }
            }

            $dataByImut[] = $imutDetail;
        }

        // Calculate summary
        $summary = $this->calculateSummary($dataByImut);

        // Prepare period label
        $periodLabel = PeriodFilter::formatPeriodLabel($tipe, $periode);

        return view('reports.unit-kerja-laporan', [
            'unit' => $unit,
            'tipe' => $tipe,
            'periode' => $periode,
            'periodLabel' => $periodLabel,
            'dateRange' => $dateRange,
            'dataByImut' => $dataByImut,
            'summary' => $summary,
            'allMonths' => $allMonths,
        ]);
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
}
