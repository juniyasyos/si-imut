<?php

namespace App\Services\Reporting;

use App\Models\ImutPenilaian;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk aggregasi data kategori berdasarkan periode dan kategori
 */
class CategoryAggregationService
{
    public function __construct(
        protected PeriodParserService $periodParser
    ) {}

    /**
     * Aggregate penilaian data berdasarkan kategori dan periode
     *
     * @param array $categoryIds - List of ImutCategory IDs
     * @param string $periode - Periode string (see PeriodParserService for formats)
     * @return array
     */
    public function aggregate(array $categoryIds, string $periode): array
    {
        try {
            $dates = $this->periodParser->parse($periode);
            $startDate = $dates['startDate'];
            $endDate = $dates['endDate'];
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }

        // Generate month list untuk periode ini
        $months = $this->periodParser->generateMonthList($startDate, $endDate);
        $monthValues = $months->pluck('value')->toArray();

        // Fetch penilaian dengan eager loading
        $penilaians = $this->fetchPenilaiansByPeriod(
            categoryIds: $categoryIds,
            monthStrings: $monthValues,
            startDate: $startDate,
            endDate: $endDate
        );

        // Group by indicator dan bulan
        $grouped = $this->groupByIndicatorAndMonth($penilaians);

        // Build results dengan summary
        $results = $this->buildResults($grouped);

        return [
            'months' => $months->toArray(),
            'results' => $results,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Fetch penilaian records matching periode dan kategori
     */
    private function fetchPenilaiansByPeriod(
        array $categoryIds,
        array $monthStrings,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $query = ImutPenilaian::with(['profile.imutData', 'laporanUnitKerja.laporanImut'])
            ->whereHas('profile.imutData', fn($q) => $q->where('status', true));

        // Filter by kategori jika ada
        if (count($categoryIds) > 0) {
            $query->whereHas('profile.imutData.categories', fn($q) => $q->whereIn('id', $categoryIds));
        }

        // Filter by periode
        if (count($monthStrings) > 0) {
            $query->whereHas('laporanUnitKerja.laporanImut', function ($q) use ($monthStrings, $startDate, $endDate) {
                $q->whereIn(
                    DB::raw("CONCAT(report_year,'-',LPAD(report_month,2,'0'))"),
                    $monthStrings
                )
                    ->orWhereBetween('assessment_period_start', [$startDate, $endDate])
                    ->orWhereBetween('assessment_period_end', [$startDate, $endDate])
                    ->orWhere(function ($q3) use ($startDate, $endDate) {
                        $q3->where('assessment_period_start', '<=', $startDate)
                            ->where('assessment_period_end', '>=', $endDate);
                    });
            });
        }

        return $query->get();
    }

    /**
     * Group penilaian by indicator dan bulan laporan
     */
    private function groupByIndicatorAndMonth(Collection $penilaians): array
    {
        $grouped = [];

        foreach ($penilaians as $p) {
            $imutDataId = $p->profile->imut_data_id;
            $laporan = $p->laporanUnitKerja->laporanImut;

            if ($laporan) {
                if ($laporan->report_year && $laporan->report_month) {
                    $m = sprintf('%04d-%02d', $laporan->report_year, $laporan->report_month);
                } else {
                    $dateForLabel = $laporan->assessment_period_end ?: $laporan->assessment_period_start;
                    $m = Carbon::parse($dateForLabel)->format('Y-m');
                }
            } else {
                $m = null;
            }

            if (!isset($grouped[$imutDataId])) {
                $grouped[$imutDataId] = [];
            }
            if (!isset($grouped[$imutDataId][$m])) {
                $grouped[$imutDataId][$m] = [];
            }

            $grouped[$imutDataId][$m][] = $p;
        }

        return $grouped;
    }

    /**
     * Build hasil summary dari grouped data
     */
    private function buildResults(array $grouped): array
    {
        $results = [];

        foreach ($grouped as $imutDataId => $itemsByMonth) {
            $allItems = collect($itemsByMonth)->flatten(1);
            $imutData = $allItems->first()->profile->imutData;

            $overallNumerator = $allItems->sum('numerator_value');
            $overallDenominator = $allItems->sum('denominator_value');
            $overallPercentage = $overallDenominator > 0 ? ($overallNumerator / $overallDenominator) * 100 : 0;

            $results[] = [
                'imut_data_id' => $imutDataId,
                'title' => $imutData->title ?? '- tanpa judul -',
                'numerator' => $overallNumerator,
                'denominator' => $overallDenominator,
                'percentage' => $overallPercentage,
                'category' => $imutData->categories?->category_name,
                'monthly_data' => $this->buildMonthlyBreakdown($itemsByMonth),
            ];
        }

        return $results;
    }

    /**
     * Build monthly breakdown untuk satu indicator
     */
    private function buildMonthlyBreakdown(array $itemsByMonth): array
    {
        $breakdown = [];

        foreach ($itemsByMonth as $month => $items) {
            $items = collect($items);
            $breakdown[$month] = [
                'numerator' => $items->sum('numerator_value'),
                'denominator' => $items->sum('denominator_value'),
                'count' => $items->count(),
            ];
        }

        return $breakdown;
    }
}
