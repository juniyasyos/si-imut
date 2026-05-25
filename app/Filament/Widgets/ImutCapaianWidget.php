<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use App\Services\Chart\ChartDataProcessorService;
use App\Services\Core\ImutSqlExpressionBuilder;
use App\Services\Chart\ImutChartSeriesService;
use App\Services\Core\ImutCalculationService;
use App\Services\DailyReport\WidgetDataService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianWidget';
    protected static ?string $heading = 'Capaian IMUT Per Kategori (Persentase)';
    protected static ?string $description = 'Grafik ini memperlihatkan persentase indikator IMUT yang berhasil memenuhi target untuk setiap kategori dalam satu laporan terpilih.';
    protected static ?int $sort = 5;
    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;
    protected int|string|array $columnSpan = 'full';

    public ?array $statistikData = null;
    public ?int $selectedLaporanId = null;

    /**
     * Handler untuk perubahan laporan yang dipilih
     */
    public function updatedSelectedLaporanId(): void
    {
        // Force widget untuk reload dengan data baru
        $this->dispatch('$refresh');
    }

    public function getChartProcessor(): ChartDataProcessorService
    {
        return app(ChartDataProcessorService::class);
    }

    protected function getChartService(): ImutChartSeriesService
    {
        return new ImutChartSeriesService();
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutCapaianWidget');
    }

    protected function getFormSchema(): array
    {
        $categories = $this->getChartService()->getCategories();
        $laporanOptions = $this->getLaporanOptions();

        return [
            Section::make('Filter Data')
                ->schema([
                    Select::make('selectedLaporanId')
                        ->label('Laporan IMUT')
                        ->options($laporanOptions)
                        ->searchable()
                        ->required()
                        ->default(array_key_first($laporanOptions))
                        ->reactive(),

                    Select::make('categories')
                        ->label('Kategori IMUT')
                        ->multiple()
                        ->options($categories)
                        ->placeholder('Semua Kategori')
                        ->helperText('Pilih kategori yang ingin ditampilkan. Grafik akan menampilkan persentase capaian per kategori.')
                        ->reactive(),

                    Select::make('status')
                        ->label('Status Laporan')
                        ->multiple()
                        ->options([
                            LaporanImut::STATUS_COMPLETE => 'Complete',
                            LaporanImut::STATUS_PROCESS => 'In Process',
                            LaporanImut::STATUS_COMINGSOON => 'Coming Soon',
                        ])
                        ->default([LaporanImut::STATUS_COMPLETE])
                        ->helperText('Filter berdasarkan status laporan yang akan ditampilkan')
                        ->reactive(),

                    Checkbox::make('show_dataLabels')
                        ->label('Tampilkan Nilai di Chart')
                        ->default(false)
                        ->reactive(),
                ])
                ->collapsible()
        ];
    }

    public function getOptions(): array
    {
        $selectedCategories = $this->filterFormData['categories'] ?? [];
        $showDataLabels = $this->filterFormData['show_dataLabels'] ?? false;

        $laporans = $this->getCachedLaporans();

        if ($laporans->isEmpty()) {
            return ApexChartConfig::noDataOptions();
        }

        $selectedLaporanId = $this->filterFormData['selectedLaporanId'] ?? $this->selectedLaporanId;
        $selectedLaporan = $laporans->firstWhere('id', $selectedLaporanId) ?? $laporans->first();

        if (!$selectedLaporan) {
            return ApexChartConfig::noDataOptions();
        }

        $this->selectedLaporanId = $selectedLaporan->id;

        $categories = $this->getChartService()->getCategories();

        if (!empty($selectedCategories)) {
            $categories = collect($categories)
                ->filter(fn($name) => in_array($name, $selectedCategories))
                ->values()
                ->toArray();
        }

        $service = app(WidgetDataService::class);
        $this->statistikData = $service->getImutCapaianStats($selectedLaporan, $categories);

        $colors = $this->getChartService()->getDefaultColors();

        $xLabels = $categories;
        $processedData = $this->getChartProcessor()->processCategoryAchievementData($selectedLaporan, $categories);

        $series = [[
            'name' => 'Persentase Capaian',
            'type' => 'bar',
            'data' => $processedData,
            'color' => $colors[0] ?? '#3b82f6',
        ]];

        $options = ApexChartConfig::defaultOptions(
            $series,
            $xLabels,
            xLabelTitle: 'Kategori',
            yLabelTitle: 'Persentase Capaian (%)',
            yAxisMin: 0,
            yAxisMax: 100,
            showDataLabels: $showDataLabels,
            chartType: 'bar'
        );

        return $options;
    }


    protected function checkIfMeetsStandard(float $achievement, float $standard, string $operator): bool
    {
        return ImutCalculationService::meetsStandard($achievement, $standard, $operator);
    }

    public function getFooter(): ?string
    {
        if (!$this->statistikData) {
            return null;
        }
        return view('filament.widgets.imut-capaian-footer', [
            'stats' => $this->statistikData,
        ])->render();
    }

    protected function getLaporanOptions(): array
    {
        $laporans = $this->getCachedLaporans();

        return $laporans->mapWithKeys(function ($laporan) {
            $period = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->format('F Y')
                : ($laporan->report_year && $laporan->report_month ? sprintf('%04d-%02d', $laporan->report_year, $laporan->report_month) : 'Unknown');

            return [$laporan->id => "{$laporan->name} - {$period}"];
        })->toArray();
    }

    protected function getCachedLaporans()
    {
        $statuses = $this->filterFormData['status'] ?? [LaporanImut::STATUS_COMPLETE];

        $cacheKey = CacheKey::imutLaporans() . '_' . implode('_', $statuses);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($statuses) {
                return LaporanImut::with([
                    'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
                ])
                    ->whereIn('status', $statuses)
                    ->orderBy('assessment_period_start', 'desc')
                    ->get();
            }
        );
    }
}
