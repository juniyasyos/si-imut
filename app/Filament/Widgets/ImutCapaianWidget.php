<?php

namespace App\Filament\Widgets;

use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Services\Chart\ChartDataProcessorService;
use App\Services\ImutCalculationService;
use App\Services\ImutChartSeriesService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianWidget';
    protected static ?string $heading = 'Analisis Capaian IMUT Per Kategori';
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

        // Generate tahun options dari data laporan yang ada
        $years = LaporanImut::selectRaw('DISTINCT YEAR(assessment_period_start) as year')
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        return [
            Section::make('Filter Data')
                ->schema([
                    Grid::make()
                        ->schema([
                            Select::make('categories')
                                ->label('Kategori IMUT')
                                ->multiple()
                                ->options($categories)
                                ->placeholder('Semua Kategori')
                                ->helperText('Pilih kategori yang ingin ditampilkan')
                                ->reactive(),

                            Select::make('year')
                                ->label('Tahun')
                                ->options(['all' => 'Semua Tahun'] + $years)
                                ->default('all')
                                ->reactive(),

                            Select::make('quarter')
                                ->label('Kuartal')
                                ->options([
                                    'all' => 'Semua Kuartal',
                                    'Q1' => 'Kuartal 1 (Jan-Mar)',
                                    'Q2' => 'Kuartal 2 (Apr-Jun)',
                                    'Q3' => 'Kuartal 3 (Jul-Sep)',
                                    'Q4' => 'Kuartal 4 (Okt-Des)',
                                ])
                                ->default('all')
                                ->visible(fn($get) => $get('year') !== 'all')
                                ->reactive(),

                        ])
                        ->columns(2),
                    Select::make('period_range')
                        ->label('Rentang Periode')
                        ->options([
                            'all' => 'Semua Data',
                            '3' => '3 Bulan Terakhir',
                            '6' => '6 Bulan Terakhir',
                            '12' => '1 Tahun Terakhir',
                            '24' => '2 Tahun Terakhir',
                        ])
                        ->default('6')
                        ->visible(fn($get) => $get('year') === 'all')
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
                        ->helperText('Filter berdasarkan status laporan')
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
        $showDataLabels = $this->filterFormData['show_dataLabels'] ?? true;

        $laporans = $this->getCachedLaporans();

        if ($laporans->isEmpty()) {
            return ApexChartConfig::noDataOptions();
        }

        // Use service untuk processing data
        $categories = $this->getChartService()->getCategories();

        // Filter categories jika ada yang dipilih
        if (!empty($selectedCategories)) {
            $categories = collect($categories)->filter(function ($name, $id) use ($selectedCategories) {
                return in_array($id, $selectedCategories);
            })->toArray();
        }

        // Hitung statistik detail
        $this->statistikData = $this->calculateDetailedStatistics($laporans, $categories);

        $colors = $this->getChartService()->getDefaultColors();

        $xLabels = $this->getChartProcessor()->generateTimeLabels($laporans);
        $processedData = $this->getChartProcessor()->processCapaianData($laporans, $categories);

        // Build series dengan default config (tanpa custom types/colors dari form)
        $series = $this->getChartProcessor()->buildChartSeries($processedData, [], $colors);

        return ApexChartConfig::defaultOptions(
            $series,
            $xLabels,
            xLabelTitle: 'IMUT Kategori',
            yLabelTitle: 'Capaian (%)',
            showDataLabels: $showDataLabels
        );
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
        $availableLaporans = $laporans->sortByDesc('assessment_period_start')->map(function($laporan) {
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

    protected function getCachedLaporans()
    {
        $year = $this->filterFormData['year'] ?? 'all';
        $quarter = $this->filterFormData['quarter'] ?? 'all';
        $periodRange = $this->filterFormData['period_range'] ?? '6';
        $statuses = $this->filterFormData['status'] ?? [LaporanImut::STATUS_COMPLETE];

        // Generate cache key
        $cacheKey = CacheKey::imutLaporans() . "_{$year}_{$quarter}_{$periodRange}_" . implode('_', $statuses);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($year, $quarter, $periodRange, $statuses) {
                $query = LaporanImut::with([
                    'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
                ]);

                // Filter berdasarkan tahun dan kuartal jika dipilih
                if ($year !== 'all') {
                    $query->whereYear('assessment_period_start', $year);

                    if ($quarter !== 'all') {
                        // Filter berdasarkan kuartal
                        $quarterMonths = [
                            'Q1' => [1, 2, 3],
                            'Q2' => [4, 5, 6],
                            'Q3' => [7, 8, 9],
                            'Q4' => [10, 11, 12],
                        ];

                        if (isset($quarterMonths[$quarter])) {
                            $query->whereIn(
                                DB::raw('MONTH(report_month)'),
                                $quarterMonths[$quarter]
                            );
                        }
                    }
                } else {
                    // Jika tidak filter per tahun, gunakan periode range
                    $startDate = match ($periodRange) {
                        '3' => now()->subMonths(3),
                        '6' => now()->subMonths(6),
                        '12' => now()->subMonths(12),
                        '24' => now()->subMonths(24),
                        'all' => now()->subYears(10),
                        default => now()->subMonths(6),
                    };

                    $query->where('assessment_period_start', '>=', $startDate);
                }

                $query->whereIn('status', $statuses)
                    ->orderBy('assessment_period_start');

                return $query->get();
            }
        );
    }
}
