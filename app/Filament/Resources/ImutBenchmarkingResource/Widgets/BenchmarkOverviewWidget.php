<?php

namespace App\Filament\Resources\ImutBenchmarkingResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BenchmarkOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBenchmarks = ImutBenchmarking::count();
        $activeBenchmarks = ImutBenchmarking::where('is_active', true)->count();
        $totalImutData = ImutData::count();
        $totalRegionTypes = RegionType::count();

        // Berapa % IMUT Data yang sudah memiliki benchmark
        $imutWithBenchmarks = ImutData::has('benchmarkings')->count();
        $coveragePercentage = $totalImutData > 0 ? round(($imutWithBenchmarks / $totalImutData) * 100, 1) : 0;

        return [
            Stat::make('Total Benchmark Data', $totalBenchmarks)
                ->description('Semua data benchmark')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Benchmark Aktif', $activeBenchmarks)
                ->description('Benchmark yang sedang berlaku')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Coverage IMUT Data', $coveragePercentage . '%')
                ->description("{$imutWithBenchmarks} dari {$totalImutData} IMUT memiliki benchmark")
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($coveragePercentage >= 80 ? 'success' : ($coveragePercentage >= 50 ? 'warning' : 'danger')),

            Stat::make('Tipe Region', $totalRegionTypes)
                ->description('Total region type tersedia')
                ->descriptionIcon('heroicon-m-map')
                ->color('gray'),
        ];
    }
}
