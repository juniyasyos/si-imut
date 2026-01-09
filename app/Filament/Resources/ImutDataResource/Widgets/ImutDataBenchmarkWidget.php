<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\RegionType;
use App\Services\ImutBenchmarkingService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ImutDataBenchmarkWidget extends BaseWidget
{
    public ?int $imutDataId = null;

    protected ImutBenchmarkingService $benchmarkingService;

    public function mount(): void
    {
        $this->benchmarkingService = app(ImutBenchmarkingService::class);
    }

    protected function getStats(): array
    {
        if (!$this->imutDataId) {
            return [];
        }

        $activeBenchmarks = ImutBenchmarking::where('imut_data_id', $this->imutDataId)
            ->where('is_active', true)
            ->count();

        $totalRegionTypes = RegionType::count();

        $coveragePercentage = $totalRegionTypes > 0 ? round(($activeBenchmarks / $totalRegionTypes) * 100, 1) : 0;

        // Get latest benchmark values per region
        $latestBenchmarks = ImutBenchmarking::where('imut_data_id', $this->imutDataId)
            ->where('is_active', true)
            ->with('regionType:id,type')
            ->orderByDesc('period_start')
            ->get()
            ->groupBy('region_type_id')
            ->map(fn($group) => $group->first());

        $stats = [
            Stat::make('Benchmark Coverage', $coveragePercentage . '%')
                ->description("{$activeBenchmarks} dari {$totalRegionTypes} region type")
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($coveragePercentage >= 80 ? 'success' : ($coveragePercentage >= 50 ? 'warning' : 'danger')),
        ];

        // Add individual benchmark values
        foreach ($latestBenchmarks as $benchmark) {
            $regionName = $benchmark->regionType->type ?? 'Unknown';
            $value = number_format($benchmark->benchmark_value, 1) . '%';

            $stats[] = Stat::make("Benchmark {$regionName}", $value)
                ->description('Nilai benchmark terkini')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color('primary');
        }

        return $stats;
    }

    /**
     * Set the IMUT Data ID for this widget
     */
    public function setImutDataId(int $imutDataId): void
    {
        $this->imutDataId = $imutDataId;
    }
}
