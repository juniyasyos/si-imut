<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Services\Benchmarking\ImutBenchmarkingService;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class BenchmarkComparisonChart extends ChartWidget
{
    protected static ?string $heading = 'Perbandingan Benchmark Antar Region';

    protected static string $color = 'info';

    public ImutData $imutData;

    protected ImutBenchmarkingService $benchmarkingService;

    public function mount(): void
    {
        $this->benchmarkingService = app(ImutBenchmarkingService::class);
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function hasFilterForm(): bool
    {
        return true;
    }

    protected function getFilters(): ?array
    {
        $years = range(now()->year, now()->year - 5);
        $yearOptions = array_combine($years, $years);

        return [
            'year' => Select::make('year')
                ->label('Tahun')
                ->options($yearOptions)
                ->default(now()->year),
        ];
    }

    protected function getData(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;

        // Get active benchmarks for this IMUT Data
        $benchmarks = ImutBenchmarking::where('imut_data_id', $this->imutData->id)
            ->where('is_active', true)
            ->whereYear('period_start', '<=', $year)
            ->where(function ($query) use ($year) {
                $query->whereNull('period_end')
                    ->orWhereYear('period_end', '>=', $year);
            })
            ->with('regionType:id,type,display_color')
            ->orderBy('benchmark_value', 'desc')
            ->get();

        if ($benchmarks->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = $benchmarks->pluck('regionType.type')->toArray();
        $data = $benchmarks->pluck('benchmark_value')->toArray();
        $colors = $benchmarks->map(function ($benchmark) {
            return $benchmark->regionType->getDisplayColorWithFallback();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Nilai Benchmark (%)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": " + context.parsed.y + "%";
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return value + "%";
                        }',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
