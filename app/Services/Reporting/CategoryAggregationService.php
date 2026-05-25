<?php

namespace App\Services\Reporting;

use Carbon\Carbon;
use App\Repositories\Interfaces\ImutPenilaianRepositoryInterface;
use App\Services\Support\PeriodParserService;
use Illuminate\Support\Collection;

/**
 * Service untuk aggregasi data kategori berdasarkan periode dan kategori
 */
class CategoryAggregationService
{
    public function __construct(
        protected PeriodParserService $periodParser,
        protected ImutPenilaianRepositoryInterface $penilaianRepository
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
        $penilaians = $this->penilaianRepository->getByCategoryPeriod($categoryIds, $monthValues, $startDate, $endDate);

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
