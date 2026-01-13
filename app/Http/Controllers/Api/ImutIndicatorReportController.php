<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\ImutDataNote;
use App\Models\ImutBenchmarking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImutIndicatorReportController extends Controller
{
    public function show(Request $request, string $indicator, string $periode): JsonResponse
    {
        // Convert slug to ID for indicator
        $imutData = ImutData::where('slug', $indicator)->first();
        $imutDataId = $imutData ? $imutData->id : null;

        // periode parameter is laporan ID
        $laporanId = $periode;

        if (!$imutDataId || !$laporanId) {
            return response()->json(['error' => 'Invalid indicator or periode'], 400);
        }

        $imutData = ImutData::with(['categories', 'unitKerja', 'profiles' => function ($q) {
            $q->latest()->limit(1);
        }])->find($imutDataId);

        $laporan = LaporanImut::with(['createdBy', 'laporanUnitKerjas.imutPenilaians.profile.imutData'])->find($laporanId);

        if (!$imutData || !$laporan) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        // Initialize filter form data with proper defaults
        $filterFormData = [
            'filter_mode' => $request->input('filter_mode', 'custom'),
            'start_year' => (int) $request->input('start_year', now()->year),
            'start_month' => (int) $request->input('start_month', 1),
            'end_year' => (int) $request->input('end_year', now()->year),
            'end_month' => (int) $request->input('end_month', now()->month),
            'quarter_year' => (int) $request->input('quarter_year', now()->year),
            'quarters' => $request->input('quarters', ['Q1']),
            'semester_year' => (int) $request->input('semester_year', now()->year),
            'semesters' => $request->input('semesters', ['S1']),
            'yearly_years' => $request->input('yearly_years', [now()->year]),
        ];

        // Load available notes
        $availableNotes = ImutDataNote::where('imut_data_id', $imutDataId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Load selected note
        $noteId = $request->get('note_id');
        $selectedNote = null;
        if ($noteId) {
            $selectedNote = $availableNotes->firstWhere('id', $noteId);
        } else {
            $selectedNote = $availableNotes->first();
        }

        // Load historical data
        $historicalData = $this->loadHistoricalData($imutDataId, $filterFormData);

        // Load benchmark data
        $benchmarkData = $this->loadBenchmarkData($imutData);

        // Load unit kerja data
        $unitKerjaData = $this->loadUnitKerjaData($laporan, $imutDataId);

        // Calculate summary
        $summary = $this->calculateSummary($unitKerjaData);

        // Computed properties
        $categoryDisplay = $this->getCategoryDisplay($imutData);
        $standardDisplay = $this->getStandardDisplay($imutData);
        $periodeDisplay = $this->getPeriodeDisplay($laporan);
        $overallPercentage = $summary['average_percentage'] ?? 0;
        $isAchieved = $overallPercentage >= $standardDisplay;

        return response()->json([
            'imutData' => $imutData,
            'laporan' => $laporan,
            'filterFormData' => $filterFormData,
            'availableNotes' => $availableNotes,
            'selectedNote' => $selectedNote,
            'historicalData' => $historicalData,
            'benchmarkData' => $benchmarkData,
            'unitKerjaData' => $unitKerjaData,
            'summary' => $summary,
            'categoryDisplay' => $categoryDisplay,
            'standardDisplay' => $standardDisplay,
            'periodeDisplay' => $periodeDisplay,
            'overallPercentage' => $overallPercentage,
            'isAchieved' => $isAchieved,
            'lastRefreshTime' => now()->format('H:i:s'),
        ]);
    }

    private function loadHistoricalData(int $imutDataId, array $filterFormData): array
    {
        $historicalData = [];

        // Get date range from filter
        [$startYear, $startMonth, $endYear, $endMonth] = $this->getFilterDateRange($filterFormData);

        // Get specific months for quarter/semester filtering
        $specificMonths = $this->getSpecificMonthsForFilter($filterFormData);

        // Query penilaians based on filter
        $penilaiansQuery = \DB::table('imut_penilaians')
            ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
            ->join('imut_profil', 'imut_profil.id', '=', 'imut_penilaians.imut_profil_id')
            ->join('imut_data', 'imut_data.id', '=', 'imut_profil.imut_data_id')
            ->where('imut_data.id', $imutDataId)
            ->where(function ($query) use ($startYear, $startMonth, $endYear, $endMonth, $specificMonths) {
                if (!empty($specificMonths)) {
                    // Use specific months filtering for quarters/semesters
                    $query->where('laporan_imuts.report_year', '=', $startYear)
                        ->whereIn('laporan_imuts.report_month', $specificMonths);
                } else {
                    // Handle range-based filtering (custom and yearly)
                    if ($startYear === $endYear) {
                        // Single year range
                        $query->where('laporan_imuts.report_year', '=', $startYear)
                            ->where('laporan_imuts.report_month', '>=', $startMonth)
                            ->where('laporan_imuts.report_month', '<=', $endMonth);
                    } else {
                        // Multi-year date ranges
                        $query->where(function ($q) use ($startYear, $startMonth, $endYear, $endMonth) {
                            $q->where('laporan_imuts.report_year', '>', $startYear)
                                ->where('laporan_imuts.report_year', '<', $endYear);
                        })
                            ->orWhere(function ($q) use ($startYear, $startMonth) {
                                $q->where('laporan_imuts.report_year', '=', $startYear)
                                    ->where('laporan_imuts.report_month', '>=', $startMonth);
                            })
                            ->orWhere(function ($q) use ($endYear, $endMonth) {
                                $q->where('laporan_imuts.report_year', '=', $endYear)
                                    ->where('laporan_imuts.report_month', '<=', $endMonth);
                            });
                    }
                }
            })
            ->select([
                'imut_penilaians.numerator_value',
                'imut_penilaians.denominator_value',
                'laporan_imuts.report_year',
                'laporan_imuts.report_month',
                \DB::raw('MONTHNAME(CONCAT(laporan_imuts.report_year, "-", LPAD(laporan_imuts.report_month, 2, "0"), "-01")) as month_name')
            ])
            ->get();

        // Group by month/year
        $groupedData = $penilaiansQuery->groupBy(function ($item) {
            return $item->report_year . '-' . str_pad($item->report_month, 2, '0', STR_PAD_LEFT);
        });

        foreach ($groupedData as $period => $periodPenilaians) {
            $totalNumerator = $periodPenilaians->sum('numerator_value');
            $totalDenominator = $periodPenilaians->sum('denominator_value');
            $percentage = $totalDenominator > 0 ? ($totalNumerator / $totalDenominator) * 100 : 0;

            $firstItem = $periodPenilaians->first();
            $monthName = $firstItem->month_name ?? 'Unknown';

            $historicalData[] = [
                'month_short' => substr($monthName, 0, 3),
                'month' => $monthName,
                'year' => $firstItem->report_year,
                'numerator' => $totalNumerator,
                'denominator' => $totalDenominator,
                'percentage' => round($percentage, 2)
            ];
        }

        // Sort by year and month
        usort($historicalData, function ($a, $b) {
            if ($a['year'] === $b['year']) {
                return $a['month'] <=> $b['month'];
            }
            return $a['year'] <=> $b['year'];
        });

        // If no data found, use mock data as fallback
        if (empty($historicalData)) {
            $historicalData = [
                [
                    'month_short' => 'Jan',
                    'month' => 'Januari',
                    'year' => 2024,
                    'numerator' => 45,
                    'denominator' => 50,
                    'percentage' => 90.00
                ],
                [
                    'month_short' => 'Feb',
                    'month' => 'Februari',
                    'year' => 2024,
                    'numerator' => 48,
                    'denominator' => 52,
                    'percentage' => 92.31
                ]
            ];
        }

        return $historicalData;
    }

    private function loadBenchmarkData(ImutData $imutData): array
    {
        $benchmarkData = [];
        $regionNames = [];

        $benchmarks = ImutBenchmarking::with('regionType')
            ->where('imut_data_id', $imutData->id)
            ->where('is_active', true)
            ->get();

        foreach ($benchmarks as $benchmark) {
            $regionName = $benchmark->region_name ?? $benchmark->regionType->type ?? 'Unknown';
            if (!in_array($regionName, $regionNames)) {
                $regionNames[] = $regionName;
            }

            $benchmarkData[] = [
                'region_name' => $regionName,
                'benchmark_value' => $benchmark->benchmark_value,
                'region_type_id' => $benchmark->region_type_id,
            ];
        }

        return $benchmarkData;
    }

    private function loadUnitKerjaData(LaporanImut $laporan, int $imutDataId): array
    {
        $unitKerjaData = [];

        foreach ($laporan->laporanUnitKerjas as $laporanUnitKerja) {
            $unitKerja = $laporanUnitKerja->unitKerja;

            // Get penilaian for this specific imut data
            $penilaian = $laporanUnitKerja->imutPenilaians->first(function ($p) use ($imutDataId) {
                return $p->profile && $p->profile->imutData &&
                    $p->profile->imut_data_id == $imutDataId;
            });

            if ($penilaian) {
                $unitKerjaData[] = [
                    'unit_kerja_id' => $unitKerja->id,
                    'unit_kerja_name' => $unitKerja->name,
                    'numerator_value' => $penilaian->numerator_value ?? 0,
                    'denominator_value' => $penilaian->denominator_value ?? 0,
                    'percentage' => $penilaian->denominator_value > 0 ?
                        round(($penilaian->numerator_value / $penilaian->denominator_value) * 100, 2) : 0,
                    'analysis' => $penilaian->analysis,
                    'recommendations' => $penilaian->recommendations,
                ];
            }
        }

        return $unitKerjaData;
    }

    private function calculateSummary(array $unitKerjaData): array
    {
        $collection = collect($unitKerjaData);

        return [
            'total_unit_kerja' => $collection->count(),
            'total_numerator' => $collection->sum('numerator_value'),
            'total_denominator' => $collection->sum('denominator_value'),
            'average_percentage' => $collection->avg('percentage') ?? 0
        ];
    }

    private function getCategoryDisplay(ImutData $imutData): string
    {
        // categories adalah relasi BelongsTo ke ImutCategory
        // Akses langsung sebagai object
        $category = $imutData->categories;

        if ($category && is_object($category)) {
            return $category->category_name ?? $category->short_name ?? 'N/A';
        }

        // Jika categories adalah JSON string (legacy), decode
        if (is_string($imutData->categories)) {
            $decoded = json_decode($imutData->categories, true);
            if (is_array($decoded)) {
                return $decoded['category_name'] ?? $decoded['short_name'] ?? 'N/A';
            }
        }

        return 'N/A';
    }

    private function getStandardDisplay(ImutData $imutData): float
    {
        // Standard/target ada di ImutProfile, bukan di ImutData
        // Ambil dari profile terbaru yang valid
        $profile = $imutData->latestProfile ?? $imutData->profiles()->latest()->first();

        if ($profile) {
            return $profile->target_value ?? 0;
        }

        return 0;
    }

    private function getPeriodeDisplay(LaporanImut $laporan): string
    {
        $startDate = Carbon::parse($laporan->assessment_period_start);
        $endDate = Carbon::parse($laporan->assessment_period_end);
        $sameMonth = $startDate->month === $endDate->month && $startDate->year === $endDate->year;

        return $sameMonth
            ? $startDate->translatedFormat('d') . ' – ' . $endDate->translatedFormat('d F Y')
            : $startDate->translatedFormat('d M') . ' – ' . $endDate->translatedFormat('d F Y');
    }

    private function getSpecificMonthsForFilter(array $filterFormData): array
    {
        $mode = $filterFormData['filter_mode'] ?? 'custom';

        switch ($mode) {
            case 'quarter':
                $quarters = $filterFormData['quarters'] ?? ['Q1'];

                // Ensure quarters is always an array
                if (!is_array($quarters)) {
                    $quarters = [$quarters];
                }

                $quarterMonths = [
                    'Q1' => [1, 2, 3],
                    'Q2' => [4, 5, 6],
                    'Q3' => [7, 8, 9],
                    'Q4' => [10, 11, 12]
                ];

                $allMonths = [];
                foreach ($quarters as $quarter) {
                    $allMonths = array_merge($allMonths, $quarterMonths[$quarter]);
                }

                return array_unique($allMonths);

            case 'semester':
                $semesters = $filterFormData['semesters'] ?? ['S1'];

                // Ensure semesters is always an array
                if (!is_array($semesters)) {
                    $semesters = [$semesters];
                }

                $semesterMonths = [
                    'S1' => [1, 2, 3, 4, 5, 6],
                    'S2' => [7, 8, 9, 10, 11, 12]
                ];

                $allMonths = [];
                foreach ($semesters as $semester) {
                    $allMonths = array_merge($allMonths, $semesterMonths[$semester]);
                }

                return array_unique($allMonths);

            default:
                // For custom and yearly, use range filtering
                return [];
        }
    }

    private function getFilterDateRange(array $filterFormData): array
    {
        $mode = $filterFormData['filter_mode'] ?? 'custom';

        switch ($mode) {
            case 'quarter':
                $year = $filterFormData['quarter_year'] ?? now()->year;
                $quarters = $filterFormData['quarters'] ?? ['Q1'];

                // Ensure quarters is always an array
                if (!is_array($quarters)) {
                    $quarters = [$quarters];
                }

                // Get min and max months from selected quarters
                $quarterMonths = [
                    'Q1' => [1, 2, 3],
                    'Q2' => [4, 5, 6],
                    'Q3' => [7, 8, 9],
                    'Q4' => [10, 11, 12]
                ];

                $allMonths = [];
                foreach ($quarters as $quarter) {
                    $allMonths = array_merge($allMonths, $quarterMonths[$quarter]);
                }

                // Sort months and get min/max
                sort($allMonths);
                return [$year, min($allMonths), $year, max($allMonths)];

            case 'semester':
                $year = $filterFormData['semester_year'] ?? now()->year;
                $semesters = $filterFormData['semesters'] ?? ['S1'];

                // Ensure semesters is always an array
                if (!is_array($semesters)) {
                    $semesters = [$semesters];
                }

                $semesterMonths = [
                    'S1' => [1, 2, 3, 4, 5, 6],
                    'S2' => [7, 8, 9, 10, 11, 12]
                ];

                $allMonths = [];
                foreach ($semesters as $semester) {
                    $allMonths = array_merge($allMonths, $semesterMonths[$semester]);
                }

                // Sort months and get min/max
                sort($allMonths);
                return [$year, min($allMonths), $year, max($allMonths)];

            case 'yearly':
                $years = $filterFormData['yearly_years'] ?? [now()->year];

                // Ensure years is always an array
                if (!is_array($years)) {
                    $years = [$years];
                }

                return [min($years), 1, max($years), 12];

            default: // custom
                return [
                    $filterFormData['start_year'] ?? now()->year,
                    $filterFormData['start_month'] ?? 1,
                    $filterFormData['end_year'] ?? now()->year,
                    $filterFormData['end_month'] ?? now()->month
                ];
        }
    }
}
