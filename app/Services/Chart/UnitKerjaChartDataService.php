<?php

namespace App\Services\Chart;

use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Services\Core\ImutCalculatorService;
use App\Support\ApexChartConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UnitKerjaChartDataService
{
    public function __construct(
        private readonly ImutCalculatorService $calculator,
        private readonly ChartDataProcessorService $chartProcessor
    ) {}

    /**
     * Process chart data for specific unit kerja
     */
    public function processUnitKerjaChartData(array $filterData = []): array
    {
        $user = Auth::user();
        $unitKerja = $user->unitKerjas->first();

        if (!$unitKerja) {
            return ApexChartConfig::noDataOptions();
        }

        $laporans = $this->getCachedLaporans($unitKerja->id);

        if ($laporans->isEmpty()) {
            return ApexChartConfig::noDataOptions();
        }

        return $this->buildUnitKerjaChartSeries($laporans, $filterData);
    }

    /**
     * Build chart series for unit kerja
     */
    public function buildUnitKerjaChartSeries(Collection $laporans, array $filterData = [], ?Collection $categories = null): array
    {
        $categories = $categories ?? ImutCategory::all();
        $series = [];
        $colors = $filterData['series_colors'] ?? [];
        $defaultColors = [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B',
            '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'
        ];

        foreach ($categories as $index => $category) {
            $categoryData = $laporans->map(function ($laporan) use ($category) {
                $penilaians = $laporan->laporanUnitKerjas
                    ->flatMap(fn($luk) => $luk->imutPenilaians)
                    ->filter(fn($penilaian) => $penilaian->imutProfil->imutData->imut_kategori_id === $category->id);

                if ($penilaians->isEmpty()) {
                    return 0;
                }

                $totalNumerator = $penilaians->sum('numerator_value');
                $totalDenominator = $penilaians->sum('denominator_value');

                return $this->calculator->calculatePercentage($totalNumerator, $totalDenominator);
            })->toArray();

            $series[] = [
                'name' => $category->short_name,
                'data' => $categoryData,
                'color' => $colors[$category->short_name] ?? $defaultColors[$index % count($defaultColors)]
            ];
        }

        return $series;
    }

    /**
     * Generate chart heading for unit kerja
     */
    public function generateUnitKerjaHeading(): ?string
    {
        $user = Auth::user();

        if (!$user) {
            return 'Capaian IMUT Unit Kerja';
        }

        $unitKerja = $user->unitKerjas->first();

        return $unitKerja
            ? 'Capaian IMUT setiap Kategori Untuk Unit ' . $unitKerja->unit_name
            : 'Capaian IMUT Unit Kerja';
    }

    /**
     * Get cached laporans for unit kerja
     */
    private function getCachedLaporans(int $unitKerjaId): Collection
    {
        return Cache::remember(
            "unit_kerja_laporans_{$unitKerjaId}",
            now()->addMinutes(30),
            fn() => LaporanImut::with(['laporanUnitKerjas.imutPenilaians.imutProfil.imutData.imutCategory'])
                ->whereHas('laporanUnitKerjas', fn($query) => $query->where('unit_kerja_id', $unitKerjaId))
                ->orderBy('assessment_period_start')
                ->get()
        );
    }
}
