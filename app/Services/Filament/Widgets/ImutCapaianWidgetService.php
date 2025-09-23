<?php

namespace App\Services\Filament\Widgets;

use App\Models\ImutCategory;
use App\Services\ImutChartSeriesService;
use Illuminate\Support\Facades\Auth;

class ImutCapaianWidgetService
{
    public function __construct(
        private ImutChartSeriesService $chartService
    ) {}

    /**
     * Check if user can view IMUT capaian widget
     */
    public function canViewCapaian(): bool
    {
        $user = Auth::user();

        return $user
            && $user->can('widget_ImutCapaianUnitKerjaWidget')
            && $user->unitKerjas()->exists();
    }

    /**
     * Get widget heading based on user's unit kerja
     */
    public function getWidgetHeading(): string
    {
        $user = Auth::user();
        $unitKerja = $user->unitKerjas->first();

        return $unitKerja
            ? 'Capaian IMUT setiap Kategori Untuk Unit ' . $unitKerja->unit_name
            : 'Capaian IMUT setiap Kategori';
    }

    /**
     * Get form schema data for filters
     */
    public function getFormSchemaData(): array
    {
        return [
            'categories' => $this->chartService->getCategories(),
            'colors' => $this->chartService->getDefaultColors()
        ];
    }

    /**
     * Get chart options based on filter data
     */
    public function getChartOptions(array $filterData = []): array
    {
        $laporans = $this->getCachedLaporans();
        $showDataLabels = $filterData['show_dataLabels'] ?? true;

        if ($laporans->isEmpty()) {
            return $this->getNoDataOptions();
        }

        return $this->buildChartOptions($laporans, $filterData, $showDataLabels);
    }

    /**
     * Get cached laporans data
     */
    private function getCachedLaporans()
    {
        // This would typically use your existing cache logic
        return collect(); // Placeholder
    }

    /**
     * Get no data chart options
     */
    private function getNoDataOptions(): array
    {
        return [
            'chart' => [
                'type' => 'column',
                'height' => 300,
            ],
            'series' => [],
            'xaxis' => [
                'categories' => []
            ],
            'noData' => [
                'text' => 'Tidak ada data tersedia'
            ]
        ];
    }

    /**
     * Build chart options from data
     */
    private function buildChartOptions($laporans, array $filterData, bool $showDataLabels): array
    {
        // This would contain your chart building logic
        // For now, returning a basic structure
        return [
            'chart' => [
                'type' => 'column',
                'height' => 400,
                'toolbar' => [
                    'show' => true
                ]
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'dataLabels' => [
                        'position' => 'top'
                    ]
                ]
            ],
            'dataLabels' => [
                'enabled' => $showDataLabels
            ],
            'series' => $this->buildSeriesData($laporans, $filterData),
            'xaxis' => [
                'categories' => $this->getXAxisCategories($laporans)
            ]
        ];
    }

    /**
     * Build series data for chart
     */
    private function buildSeriesData($laporans, array $filterData): array
    {
        // Placeholder for series building logic
        return [];
    }

    /**
     * Get X-axis categories
     */
    private function getXAxisCategories($laporans): array
    {
        // Placeholder for category building logic
        return [];
    }

    /**
     * Get default chart colors
     */
    public function getDefaultColors(): array
    {
        return $this->chartService->getDefaultColors();
    }

    /**
     * Get available categories
     */
    public function getCategories(): array
    {
        return $this->chartService->getCategories();
    }
}
