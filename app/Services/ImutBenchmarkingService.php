<?php

namespace App\Services;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ImutBenchmarkingService
{
    /**
     * Mendapatkan benchmark untuk IMUT Data tertentu pada periode tertentu
     *
     * @param int $imutDataId
     * @param int|array $regionTypeId
     * @param Carbon $date
     * @return Collection
     */
    public function getBenchmarkForPeriod(int $imutDataId, int|array $regionTypeId, Carbon $date): Collection
    {
        return ImutBenchmarking::query()
            ->forIndicator($imutDataId)
            ->forRegion($regionTypeId)
            ->activeForPeriod($date)
            ->with('regionType:id,type,display_color,chart_type')
            ->orderByDesc('period_start')
            ->get();
    }

    /**
     * Mendapatkan benchmark untuk semua IMUT Data dalam kategori tertentu
     *
     * @param string $categoryShortName
     * @param int|array $regionTypeId
     * @param Carbon $date
     * @return Collection
     */
    public function getBenchmarkForCategory(string $categoryShortName, int|array $regionTypeId, Carbon $date): Collection
    {
        $imutDataIds = ImutData::whereHas('categories', function ($query) use ($categoryShortName) {
            $query->where('short_name', $categoryShortName);
        })->pluck('id');

        return ImutBenchmarking::query()
            ->whereIn('imut_data_id', $imutDataIds)
            ->forRegion($regionTypeId)
            ->activeForPeriod($date)
            ->with(['imutData:id,title', 'regionType:id,type,display_color,chart_type'])
            ->orderByDesc('period_start')
            ->get();
    }

    /**
     * Mendapatkan data benchmark untuk chart dalam format yang siap pakai
     *
     * @param int $imutDataId
     * @param int $year
     * @param array|null $regionTypeIds
     * @return array
     */
    public function getBenchmarkChartData(int $imutDataId, int $year, ?array $regionTypeIds = null): array
    {
        $query = ImutBenchmarking::query()
            ->forIndicator($imutDataId)
            ->forYearMonth($year)
            ->where('is_active', true)
            ->with('regionType:id,type,display_color,chart_type');

        if ($regionTypeIds) {
            $query->forRegion($regionTypeIds);
        }

        $benchmarks = $query->get();

        $series = [];
        $categories = [];

        // Group by region type
        $groupedByRegion = $benchmarks->groupBy('region_type_id');

        foreach ($groupedByRegion as $regionTypeId => $regionBenchmarks) {
            $regionType = $regionBenchmarks->first()->regionType;

            $data = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthlyBenchmark = $regionBenchmarks->filter(function ($benchmark) use ($year, $month) {
                    $periodStart = Carbon::parse($benchmark->period_start);
                    $checkDate = Carbon::create($year, $month, 1);

                    return $benchmark->isValidForPeriod($checkDate);
                })->first();

                $data[] = $monthlyBenchmark ? $monthlyBenchmark->benchmark_value : null;
            }

            $series[] = [
                'name' => $regionType->type,
                'type' => $regionType->getChartTypeWithFallback(),
                'data' => $data,
                'color' => $regionType->getDisplayColorWithFallback(),
            ];
        }

        // Categories untuk bulan
        for ($month = 1; $month <= 12; $month++) {
            $categories[] = Carbon::create($year, $month, 1)->format('M Y');
        }

        return [
            'series' => $series,
            'categories' => $categories,
        ];
    }

    /**
     * Menyimpan atau memperbarui benchmark data
     *
     * @param array $data
     * @return ImutBenchmarking
     */
    public function createOrUpdateBenchmark(array $data): ImutBenchmarking
    {
        // Set created_by dan updated_by
        $data['created_by'] = $data['created_by'] ?? auth()->id();
        $data['updated_by'] = auth()->id();

        return ImutBenchmarking::create($data);
    }

    /**
     * Mendapatkan statistik benchmark coverage
     *
     * @return array
     */
    public function getBenchmarkCoverage(): array
    {
        $totalImutData = ImutData::count();
        $imutWithBenchmarks = ImutData::has('benchmarkings')->count();
        $totalRegionTypes = RegionType::count();

        // Coverage per region type
        $regionTypeCoverage = [];
        foreach (RegionType::all() as $regionType) {
            $imutWithThisRegionBenchmark = ImutData::whereHas('benchmarkings', function ($query) use ($regionType) {
                $query->where('region_type_id', $regionType->id);
            })->count();

            $regionTypeCoverage[] = [
                'region_type' => $regionType->type,
                'coverage_count' => $imutWithThisRegionBenchmark,
                'coverage_percentage' => $totalImutData > 0 ? round(($imutWithThisRegionBenchmark / $totalImutData) * 100, 1) : 0,
            ];
        }

        return [
            'total_imut_data' => $totalImutData,
            'imut_with_benchmarks' => $imutWithBenchmarks,
            'overall_coverage_percentage' => $totalImutData > 0 ? round(($imutWithBenchmarks / $totalImutData) * 100, 1) : 0,
            'total_region_types' => $totalRegionTypes,
            'region_type_coverage' => $regionTypeCoverage,
        ];
    }

    /**
     * Mendapatkan IMUT Data yang belum memiliki benchmark untuk region tertentu
     *
     * @param int|null $regionTypeId
     * @return Collection
     */
    public function getImutDataWithoutBenchmark(?int $regionTypeId = null): Collection
    {
        $query = ImutData::query();

        if ($regionTypeId) {
            $query->whereDoesntHave('benchmarkings', function ($q) use ($regionTypeId) {
                $q->where('region_type_id', $regionTypeId);
            });
        } else {
            $query->doesntHave('benchmarkings');
        }

        return $query->with('categories')->get();
    }

    /**
     * Bulk create benchmark untuk multiple IMUT Data
     *
     * @param array $imutDataIds
     * @param int $regionTypeId
     * @param float $benchmarkValue
     * @param Carbon $periodStart
     * @param Carbon|null $periodEnd
     * @return int
     */
    public function bulkCreateBenchmarks(
        array $imutDataIds,
        int $regionTypeId,
        float $benchmarkValue,
        Carbon $periodStart,
        ?Carbon $periodEnd = null
    ): int {
        $regionType = RegionType::find($regionTypeId);

        $createdCount = 0;
        foreach ($imutDataIds as $imutDataId) {
            $this->createOrUpdateBenchmark([
                'imut_data_id' => $imutDataId,
                'region_type_id' => $regionTypeId,
                'benchmark_value' => $benchmarkValue,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'is_active' => true,
                'notes' => 'Bulk created benchmark',
            ]);
            $createdCount++;
        }

        return $createdCount;
    }
}
