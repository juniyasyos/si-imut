<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\ImutData;
use App\Models\ImutDataNote;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\RegionType;
use App\Repositories\Interfaces\LaporanRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class ImutDataApiController extends Controller
{
    private LaporanRepositoryInterface $laporanRepository;

    public function __construct(LaporanRepositoryInterface $laporanRepository)
    {
        $this->laporanRepository = $laporanRepository;
    }

    public function summary(Request $request, $imutDataId)
    {
        $imutData = ImutData::findOrFail($imutDataId);

        // Use the existing query builder for consistency
        $query = $this->laporanRepository->getSummaryByImutDataGrouped($imutDataId);

        $records = $query->get();

        // Get region types for dynamic columns
        $regionTypes = RegionType::all();

        // Calculate summaries
        $totalN = $records->sum('total_numerator');
        $totalD = $records->sum('total_denominator');
        $totalPercentage = $totalD > 0 ? round(($totalN / $totalD) * 100, 2) : 0;
        $totalUnits = $records->sum('unit_count');

        // Prepare data for response
        $data = [
            'records' => $records->map(function ($record) use ($regionTypes) {
                $recordData = [
                    'laporan_name' => $record->laporan_name,
                    'periode_pengisian' => $record->periode_pengisian,
                    'laporan_status' => $record->laporan_status,
                    'total_numerator' => $record->total_numerator,
                    'total_denominator' => $record->total_denominator,
                    'percentage' => $record->percentage,
                    'imut_standard' => $record->imut_standard,
                    'unit_count' => $record->unit_count,
                ];

                foreach ($regionTypes as $regionType) {
                    $recordData["benchmark_{$regionType->id}"] = $record->{"benchmark_{$regionType->id}"};
                }

                return $recordData;
            }),
            'region_types' => $regionTypes->map(function ($regionType) {
                return [
                    'id' => $regionType->id,
                    'type' => $regionType->type,
                ];
            }),
            'summary' => [
                'total_numerator' => $totalN,
                'total_denominator' => $totalD,
                'total_percentage' => $totalPercentage,
                'total_units' => $totalUnits,
            ],
        ];

        return response()->json($data);
    }

    public function notes(Request $request, $imutDataId)
    {
        $notes = ImutDataNote::where('imut_data_id', $imutDataId)
            ->with(['laporanImuts', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $notes->map(function ($note) {
            return [
                'id' => $note->id,
                'period_display' => $note->period_display,
                'analysis' => $note->analysis,
                'recommendation' => $note->recommendation,
                'additional_notes' => $note->additional_notes,
                'created_at' => $note->created_at->format('d M Y H:i'),
                'user_name' => $note->creator->name ?? 'Unknown',
            ];
        });

        return response()->json(['notes' => $data]);
    }

    public function reportData(Request $request, $indicator, $periode)
    {
        $imutData = ImutData::where('slug', $indicator)->orWhere('id', $indicator)->firstOrFail();
        $laporan = LaporanImut::findOrFail($periode);

        // Get the valid profile for this report period
        $profile = $imutData->profileForLaporan($laporan);

        // Get region types for benchmark data
        $regionTypes = RegionType::all();

        // Get filter parameters from request
        $filterMode = $request->get('filter_mode', 'year');
        $selectedNoteId = $request->get('selected_note_id');

        // Build date range based on filter mode
        [$startDate, $endDate] = $this->getFilterDateRange($request, $filterMode, $laporan);

        // Get historical data using the correct query with date filter
        $historicalQuery = $this->laporanRepository->getSummaryByImutDataGrouped($imutData->id);

        // Apply date range filter
        if ($startDate && $endDate) {
            $historicalQuery->whereBetween('laporan_imuts.assessment_period_start', [$startDate, $endDate]);
        }

        $historicalData = $historicalQuery->get()->map(function ($item) use ($laporan, $imutData, $regionTypes) {
            $start = $item->assessment_period_start ? Carbon::parse($item->assessment_period_start) : $laporan->assessment_period_start;
            $end = $item->assessment_period_end ? Carbon::parse($item->assessment_period_end) : $laporan->assessment_period_end;

            // Get benchmark data for all region types
            $benchmarks = [];
            foreach ($regionTypes as $regionType) {
                $benchmarks[$regionType->type] = $item->{"benchmark_{$regionType->id}"};
            }

            return [
                'id' => $item->laporan_imut_id,
                'month_short' => $start ? $start->format('M') : 'Jan',
                'year' => $start ? $start->format('Y') : '2024',
                'month' => $start ? $start->format('F') : 'January',
                'numerator' => $item->total_numerator,
                'denominator' => $item->total_denominator,
                'percentage' => round($item->percentage, 2),
                'benchmarks' => $benchmarks,
                'is_current' => $item->laporan_imut_id == $laporan->id,
                'assessment_period_start' => $start ? $start->toDateString() : null,
            ];
        })->sortBy('assessment_period_start')->values();

        // Summary
        $currentData = $historicalData->firstWhere('is_current', true);
        $summary = [
            'total_unit_kerja' => $currentData ? $currentData['unit_count'] ?? 0 : 0,
            'total_numerator' => $currentData ? $currentData['numerator'] : 0,
            'total_denominator' => $currentData ? $currentData['denominator'] : 0,
            'average_percentage' => $currentData ? $currentData['percentage'] : null,
        ];

        // Unit kerja data (simplified)
        $unitKerjaData = [];

        // Available notes
        $availableNotes = ImutDataNote::where('imut_data_id', $imutData->id)
            ->whereRelation('laporanImuts', 'id', $laporan->id)
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'period_display' => $note->period_display,
                    'analysis' => $note->analysis,
                    'recommendation' => $note->recommendation,
                    'additional_notes' => $note->additional_notes,
                ];
            });

        // Period labels
        $periodLabels = [
            'q1' => 'Januari - Maret',
            'q2' => 'April - Juni',
            'q3' => 'Juli - September',
            'q4' => 'Oktober - Desember',
            'year' => 'Tahunan',
        ];

        // Get selected note if provided
        $selectedNote = null;
        if ($selectedNoteId) {
            $selectedNote = ImutDataNote::find($selectedNoteId);
            if ($selectedNote) {
                $selectedNote = [
                    'id' => $selectedNote->id,
                    'period_display' => $selectedNote->period_display,
                    'analysis' => $selectedNote->analysis,
                    'recommendation' => $selectedNote->recommendation,
                    'additional_notes' => $selectedNote->additional_notes,
                ];
            }
        }

        return response()->json([
            'periodLabels' => $periodLabels,
            'imutData' => [
                'id' => $imutData->id,
                'title' => $imutData->title,
                'definition' => $profile ? $profile->operational_definition : $imutData->description,
                'categories' => $imutData->categories?->category_name ?? '-',
                'standard' => $profile ? $profile->target_value : null,
                'numerator_description' => $profile ? $profile->numerator_formula : 'Numerator',
                'denominator_description' => $profile ? $profile->denominator_formula : 'Denominator',
            ],
            'laporan' => [
                'id' => $laporan->id,
                'name' => $laporan->name,
                'created_by' => $laporan->createdBy?->name ?? null,
            ],
            'summary' => $summary,
            'historicalData' => $historicalData,
            'unitKerjaData' => $unitKerjaData,
            'availableNotes' => $availableNotes,
            'selectedNote' => $selectedNote,
        ]);
    }

    /**
     * Get date range based on filter mode and parameters
     */
    private function getFilterDateRange(Request $request, string $filterMode, LaporanImut $laporan): array
    {
        switch ($filterMode) {
            case 'quarter':
                $year = $request->get('quarter_year', now()->year);
                $quarters = $request->get('quarters', ['Q1']);

                // Convert quarters to months
                $quarterMonths = [
                    'Q1' => [1, 2, 3],
                    'Q2' => [4, 5, 6],
                    'Q3' => [7, 8, 9],
                    'Q4' => [10, 11, 12]
                ];

                $allMonths = [];
                foreach ((array)$quarters as $quarter) {
                    if (isset($quarterMonths[$quarter])) {
                        $allMonths = array_merge($allMonths, $quarterMonths[$quarter]);
                    }
                }

                if (empty($allMonths)) return [null, null];

                sort($allMonths);
                $startDate = Carbon::create($year, min($allMonths), 1)->startOfMonth();
                $endDate = Carbon::create($year, max($allMonths), 1)->endOfMonth();

                return [$startDate, $endDate];

            case 'semester':
                $year = $request->get('semester_year', now()->year);
                $semesters = $request->get('semesters', ['S1']);

                $semesterMonths = [
                    'S1' => [1, 2, 3, 4, 5, 6],
                    'S2' => [7, 8, 9, 10, 11, 12]
                ];

                $allMonths = [];
                foreach ((array)$semesters as $semester) {
                    if (isset($semesterMonths[$semester])) {
                        $allMonths = array_merge($allMonths, $semesterMonths[$semester]);
                    }
                }

                if (empty($allMonths)) return [null, null];

                sort($allMonths);
                $startDate = Carbon::create($year, min($allMonths), 1)->startOfMonth();
                $endDate = Carbon::create($year, max($allMonths), 1)->endOfMonth();

                return [$startDate, $endDate];

            case 'yearly':
                $years = $request->get('yearly_years', [now()->year]);
                if (empty($years)) return [null, null];

                $years = (array)$years;
                sort($years);

                $startDate = Carbon::create(min($years), 1, 1)->startOfYear();
                $endDate = Carbon::create(max($years), 12, 31)->endOfYear();

                return [$startDate, $endDate];

            case 'custom':
            default:
                $startMonth = $request->get('start_month', 1);
                $startYear = $request->get('start_year', now()->year);
                $endMonth = $request->get('end_month', 12);
                $endYear = $request->get('end_year', now()->year);

                $startDate = Carbon::create($startYear, $startMonth, 1)->startOfMonth();
                $endDate = Carbon::create($endYear, $endMonth, 1)->endOfMonth();

                return [$startDate, $endDate];
        }
    }
}
