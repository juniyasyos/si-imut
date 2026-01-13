<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImutData;
use App\Models\ImutDataNote;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\RegionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class ImutDataApiController extends Controller
{
    public function summary(Request $request, $imutDataId)
    {
        $imutData = ImutData::findOrFail($imutDataId);

        // Use the existing query builder for consistency
        $query = \App\Models\LaporanUnitKerja::getSummaryByImutDataGrouped($imutDataId);

        $records = $query->get();

        // Get region types for dynamic columns
        $regionTypes = \App\Models\RegionType::all();

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
            ->with(['laporanImut', 'user'])
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
                'user_name' => $note->user->name ?? 'Unknown',
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

        // Get historical data using the correct query
        $historicalQuery = \App\Models\LaporanUnitKerja::getSummaryByImutDataGrouped($imutData->id);
        $historicalData = $historicalQuery->get()->map(function ($item) use ($laporan, $imutData, $regionTypes) {
            $start = $item->assessment_period_start ? \Carbon\Carbon::parse($item->assessment_period_start) : $laporan->assessment_period_start;
            $end = $item->assessment_period_end ? \Carbon\Carbon::parse($item->assessment_period_end) : $laporan->assessment_period_end;

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
            ];
        });

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
            ->whereJsonContains('related_laporan_ids', $laporan->id)
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
        ]);
    }
}
