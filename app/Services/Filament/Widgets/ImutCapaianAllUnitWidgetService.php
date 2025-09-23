<?php

namespace App\Services\Filament\Widgets;

use App\Models\LaporanImut;
use App\Models\User;
use App\Services\ImutChartSeriesService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ImutCapaianAllUnitWidgetService
{
    public function __construct(
        private ImutChartSeriesService $chartService
    ) {}

    public function canView(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // @phpstan-ignore-next-line
        return $user->can('widget_ImutCapaianWidget');
    }

    public function getCategories(): Collection
    {
        return collect($this->chartService->getCategories());
    }

    public function getDefaultColors(): array
    {
        return $this->chartService->getDefaultColors();
    }

    public function getFormSchema(): array
    {
        $categories = $this->getCategories();
        $colors = $this->getDefaultColors();

        $categoryFields = $categories->values()->map(function ($shortName, $i) use ($colors) {
            return [
                'name' => $shortName,
                'default_color' => $colors[$i % count($colors)]
            ];
        })->toArray();

        return [
            'show_dataLabels' => false,
            'categories' => $categoryFields
        ];
    }

    public function getChartOptions(array $filterData = []): array
    {
        $laporans = $this->getCachedLaporans();
        $showDataLabels = $filterData['show_dataLabels'] ?? true;

        if ($laporans->isEmpty()) {
            return ApexChartConfig::noDataOptions();
        }

        $xLabels = $this->generateXLabels($laporans);
        $series = $this->chartService->buildSeries($laporans, $filterData);

        return ApexChartConfig::defaultOptions(
            $series,
            $xLabels,
            xLabelTitle: 'IMUT Kategori',
            yLabelTitle: 'Capaian (%)',
            showDataLabels: $showDataLabels
        );
    }

    private function getCachedLaporans(): Collection
    {
        return Cache::remember(
            CacheKey::imutLaporans(),
            now()->addMinutes(5),
            fn() => LaporanImut::with([
                'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
            ])
                ->where('assessment_period_start', '>=', now()->subMonths(6))
                ->where('status', [LaporanImut::STATUS_COMPLETE, LaporanImut::STATUS_COMINGSOON])
                ->orderBy('assessment_period_start')
                ->get()
        );
    }

    private function generateXLabels(Collection $laporans): array
    {
        return $laporans->map(function ($laporan) {
            $start = $laporan->assessment_period_start ? Carbon::parse($laporan->assessment_period_start) : null;
            $end = $laporan->assessment_period_end ? Carbon::parse($laporan->assessment_period_end) : null;

            if (!$start || !$end) {
                return 'Tidak diketahui';
            }

            return $start->month === $end->month
                ? $start->day . ' - ' . $end->day . ' ' . $start->translatedFormat('F Y')
                : $start->translatedFormat('j F') . ' - ' . $end->translatedFormat('j F Y');
        })->toArray();
    }
}
