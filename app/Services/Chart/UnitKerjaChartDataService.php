<?php

namespace App\Services\Chart;

use App\Domains\Imut\Models\ImutCategory;
use App\Domains\Imut\Presenters\ImutCapaianPresenter;
use App\Domains\Imut\Queries\ImutCapaianByUnitSpec;
use App\Domains\Reporting\Models\LaporanImut;
use App\Services\Calculator\ImutCalculatorService;
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
        $dataset = app(ImutCapaianByUnitSpec::class)->build($laporans, $categories);

        $seriesDataset = array_map(function (array $series) {
            $values = array_map(function (array $point) {
                return $this->calculator->calculatePercentage($point['numerator'], $point['denominator']);
            }, $series['points']);

            return [
                'name' => $series['name'],
                'values' => $values,
            ];
        }, $dataset);

        $presenter = app(ImutCapaianPresenter::class);

        return $presenter->present($seriesDataset, $filterData['series_colors'] ?? []);
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
