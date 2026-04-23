<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use App\Services\Chart\ChartDataProcessorService;
use App\Services\ImutCalculationService;
use App\Services\ImutChartSeriesService;
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

        // Hitung statistik detail untuk laporan yang dipilih
        $this->statistikData = $this->calculateDetailedStatistics($laporans, $categories);

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

    protected function calculateDetailedStatistics($laporans, $categories): array
    {
        // Default: ambil laporan terbaru jika tidak ada yang dipilih
        if (!$this->selectedLaporanId) {
            $latestLaporan = $laporans->sortByDesc('assessment_period_start')->first();
            $this->selectedLaporanId = $latestLaporan?->id;
        }

        // Ambil laporan yang dipilih
        $selectedLaporan = $laporans->firstWhere('id', $this->selectedLaporanId);

        if (!$selectedLaporan) {
            // Fallback ke terbaru jika tidak ketemu
            $selectedLaporan = $laporans->sortByDesc('assessment_period_start')->first();
            $this->selectedLaporanId = $selectedLaporan?->id;
        }

        if (!$selectedLaporan) {
            return [
                'total_categories' => count($categories),
                'categories_detail' => [],
                'total_imut_indicators' => 0,
                'imut_meeting_standard' => 0,
                'imut_below_standard' => 0,
                'overall_achievement' => 0,
                'available_laporans' => [],
                'selected_laporan_id' => null,
            ];
        }

        // Gunakan HANYA laporan yang dipilih
        $selectedLaporanCollection = collect([$selectedLaporan]);

        // Simpan daftar laporan yang tersedia untuk dropdown
        $availableLaporans = $laporans->sortByDesc('assessment_period_start')->map(function ($laporan) {
            return [
                'id' => $laporan->id,
                'name' => $laporan->name,
                'period' => $laporan->assessment_period_start->format('F Y'),
            ];
        })->values()->toArray();

        $stats = [
            'total_categories' => count($categories),
            'categories_detail' => [],
            'total_imut_indicators' => 0,
            'imut_meeting_standard' => 0,
            'imut_below_standard' => 0,
            'overall_achievement' => 0,
            'laporan_used' => $selectedLaporan->name,
            'laporan_period' => $selectedLaporan->assessment_period_start->format('F Y'),
            'available_laporans' => $availableLaporans,
            'selected_laporan_id' => $this->selectedLaporanId,
        ];

        foreach ($categories as $categoryShortName) {
            $categoryStats = [
                'category_id' => $categoryShortName,
                'category_name' => $categoryShortName,
                'total_imut' => 0,
                'imut_meeting_standard' => 0,
                'imut_below_standard' => 0,
                'achievement_percentage' => 0,
            ];

            // Kumpulkan data dari laporan yang dipilih
            $imutDataMap = [];

            foreach ($selectedLaporanCollection as $laporan) {
                foreach ($laporan->laporanUnitKerjas as $laporanUnitKerja) {
                    foreach ($laporanUnitKerja->imutPenilaians as $penilaian) {
                        $imutData = $penilaian->profile->imutData ?? null;

                        if (!$imutData) continue;

                        // Cek apakah imut ini termasuk kategori yang sedang diproses
                        $imutCategory = $imutData->categories;
                        if (!$imutCategory || $imutCategory->short_name != $categoryShortName) {
                            continue;
                        }

                        $imutId = $imutData->id;

                        // Inisialisasi jika belum ada
                        if (!isset($imutDataMap[$imutId])) {
                            $imutDataMap[$imutId] = [
                                'title' => $imutData->title,
                                'standard' => $penilaian->profile->target_value ?? 0,
                                'operator' => $penilaian->profile->target_operator ?? '>=',
                                'total_numerator' => 0,
                                'total_denominator' => 0,
                            ];
                        }

                        // Akumulasi numerator dan denominator dari semua unit
                        if (
                            $penilaian->numerator_value !== null &&
                            $penilaian->denominator_value !== null &&
                            $penilaian->denominator_value != 0
                        ) {
                            $imutDataMap[$imutId]['total_numerator'] += $penilaian->numerator_value;
                            $imutDataMap[$imutId]['total_denominator'] += $penilaian->denominator_value;
                        }
                    }
                }
            }

            // Hitung achievement untuk IMUT yang ADA di laporan (bukan semua dari database)
            foreach ($imutDataMap as $imutId => $data) {
                $categoryStats['total_imut']++;

                // Jika denominator 0, anggap tidak memenuhi standar
                if ($data['total_denominator'] == 0) {
                    $categoryStats['imut_below_standard']++;
                    continue;
                }

                $achievement = ImutCalculationService::calculatePercentage(
                    $data['total_numerator'],
                    $data['total_denominator']
                );

                // Cek apakah memenuhi standard
                $meetsStandard = $this->checkIfMeetsStandard(
                    $achievement,
                    $data['standard'],
                    $data['operator']
                );

                if ($meetsStandard) {
                    $categoryStats['imut_meeting_standard']++;
                } else {
                    $categoryStats['imut_below_standard']++;
                }
            }

            // Hitung persentase kategori yang memenuhi standard
            if ($categoryStats['total_imut'] > 0) {
                $categoryStats['achievement_percentage'] = round(
                    ($categoryStats['imut_meeting_standard'] / $categoryStats['total_imut']) * 100,
                    2
                );
            }

            $stats['categories_detail'][] = $categoryStats;
            $stats['total_imut_indicators'] += $categoryStats['total_imut'];
            $stats['imut_meeting_standard'] += $categoryStats['imut_meeting_standard'];
            $stats['imut_below_standard'] += $categoryStats['imut_below_standard'];
        }

        // Hitung overall achievement
        if ($stats['total_imut_indicators'] > 0) {
            $stats['overall_achievement'] = round(
                ($stats['imut_meeting_standard'] / $stats['total_imut_indicators']) * 100,
                2
            );
        }

        return $stats;
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
