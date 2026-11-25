<?php

namespace App\Filament\Widgets;

use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use App\Services\Chart\ImutPeriodService;
use App\Services\Core\ImutCalculationService;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ImutTidakTercapai extends Widget
{
    use WithPagination;

    protected static string $view = 'filament.widgets.imut-tidak-tercapai-widget';

    protected static ?int $sort = 7;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    public string $periodType = 'month';

    public ?string $selectedPeriod = null;

    public string $search = '';

    public string $filterCategoryId = 'all';

    public string|int $perPage = 10;

    // State modal export Excel
    public bool $showExportModal = false;

    public string $exportPeriodType = 'month';

    public ?string $exportPeriod = null;

    /** @var array<int|string> */
    public array $exportCategoryIds = [];

    /** @var array<int|string> */
    public array $exportUnitKerjaIds = [];

    public string $exportUnitSearch = '';

    public string $exportSearch = '';

    public bool $exportMergeUnits = true;

    protected $queryString = [
        'periodType' => ['except' => 'month'],
        'selectedPeriod' => ['except' => null],
        'search' => ['except' => ''],
        'filterCategoryId' => ['except' => 'all'],
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutTercapai') ?? false;
    }

    public function mount(): void
    {
        $this->periodType = 'month';

        if (! $this->selectedPeriod) {
            $context = $this->periodContext;
            $this->selectedPeriod = $context['selectedPeriod'];
        }
    }

    public function updatingPeriodType(): void
    {
        $this->selectedPeriod = null;
        $this->resetPage('belowPage');
    }

    public function updatingSelectedPeriod(): void
    {
        $this->resetPage('belowPage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage('belowPage');
    }

    public function updatingFilterCategoryId(): void
    {
        $this->resetPage('belowPage');
    }

    public function updatingPerPage(): void
    {
        $this->resetPage('belowPage');
    }

    public function resetFilter(): void
    {
        $this->search = '';
        $this->filterCategoryId = 'all';
        $this->resetPage('belowPage');
    }

    public function getAvailableLaporansProperty(): Collection
    {
        return LaporanImut::select(['id', 'name', 'report_month', 'report_year', 'assessment_period_start', 'assessment_period_end', 'status'])
            ->orderByDesc('assessment_period_start')
            ->get();
    }

    public function getAvailableCategoriesProperty(): Collection
    {
        return ImutCategory::orderBy('category_name')
            ->get(['id', 'short_name', 'category_name']);
    }

    public function getPeriodContextProperty(): array
    {
        $context = app(ImutPeriodService::class)->resolvePeriodContext(
            $this->availableLaporans,
            $this->periodType,
            $this->selectedPeriod
        );

        if ($context['selectedPeriod'] && $this->selectedPeriod !== $context['selectedPeriod']) {
            $this->selectedPeriod = $context['selectedPeriod'];
        }

        return $context;
    }

    public function getLaporan(): ?LaporanImut
    {
        return $this->periodContext['primaryLaporan'];
    }

    /**
     * Query agregasi periode (Opsi A):
     * Menjumlahkan numerator & denominator per (unit_kerja_id, imut_data_id) selama periode terpilih,
     * lalu mengambil indikator yang nilai agregatnya belum memenuhi standar.
     */
    public static function buildAggregatedBelowStandardQuery(
        int|array $laporanIds,
        string $search = '',
        string $filterCategoryId = 'all',
        array $categoryIds = [],
        array $unitKerjaIds = []
    ): Builder {
        $ids = is_array($laporanIds)
            ? array_values(array_filter(array_map('intval', $laporanIds)))
            : [$laporanIds];

        $categoryIds = array_values(array_filter(array_map('intval', $categoryIds)));
        $unitKerjaIds = array_values(array_filter(array_map('intval', $unitKerjaIds)));

        $aggPercentageExpr = ImutCalculationService::percentageExpression(
            'SUM(imut_penilaians.numerator_value)',
            'NULLIF(SUM(imut_penilaians.denominator_value), 0)',
            'capaian_percentage'
        );

        $aggMeetsStandardCondition = ImutCalculationService::meetsStandardExpression(
            $aggPercentageExpr,
            'imut_profil.target_value',
            'imut_profil.target_operator'
        );

        $query = LaporanUnitKerja::query()
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->leftJoin('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->whereNotNull('imut_penilaians.numerator_value')
            ->whereNotNull('imut_penilaians.denominator_value')
            ->where('imut_penilaians.denominator_value', '!=', 0);

        if (empty($ids)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('laporan_unit_kerjas.laporan_imut_id', $ids);
        }

        if (! empty(trim($search))) {
            $searchTerm = '%'.trim($search).'%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('unit_kerja.unit_name', 'like', $searchTerm)
                    ->orWhere('imut_data.title', 'like', $searchTerm)
                    ->orWhere('imut_kategori.short_name', 'like', $searchTerm)
                    ->orWhere('imut_kategori.category_name', 'like', $searchTerm);
            });
        }

        if (! empty($categoryIds)) {
            $query->whereIn('imut_data.imut_kategori_id', $categoryIds);
        } elseif ($filterCategoryId !== 'all' && $filterCategoryId !== '') {
            $query->where('imut_data.imut_kategori_id', (int) $filterCategoryId);
        }

        if (! empty($unitKerjaIds)) {
            $query->whereIn('laporan_unit_kerjas.unit_kerja_id', $unitKerjaIds);
        }

        return $query
            ->select([
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                DB::raw('MAX(laporan_unit_kerjas.laporan_imut_id) as laporan_imut_id'),
                'imut_data.id as imut_data_id',
                'imut_data.title as indicator_title',
                'imut_kategori.id as category_id',
                'imut_kategori.short_name as category_short_name',
                'imut_kategori.category_name as category_name',
                'imut_profil.target_operator',
                'imut_profil.target_value',
                DB::raw('COUNT(imut_penilaians.id) as months_count'),
                DB::raw('SUM(imut_penilaians.numerator_value) as numerator_value'),
                DB::raw('SUM(imut_penilaians.denominator_value) as denominator_value'),
                DB::raw($aggPercentageExpr),
            ])
            ->groupBy(
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                'imut_data.id',
                'imut_data.title',
                'imut_kategori.id',
                'imut_kategori.short_name',
                'imut_kategori.category_name',
                'imut_profil.target_operator',
                'imut_profil.target_value'
            )
            ->havingRaw("SUM(imut_penilaians.denominator_value) > 0 AND NOT {$aggMeetsStandardCondition}")
            ->orderBy('unit_kerja.unit_name')
            ->orderBy('capaian_percentage')
            ->orderBy('imut_data.title');
    }

    /**
     * Kelompokkan collection hasil query berdasarkan Unit Kerja.
     */
    public static function groupRowsByUnit(Collection $rows): Collection
    {
        return $rows
            ->groupBy('unit_kerja_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                return (object) [
                    'unit_kerja_id' => $first->unit_kerja_id,
                    'unit_name' => $first->unit_name,
                    'laporan_imut_id' => $first->laporan_imut_id,
                    'below_count' => $items->count(),
                    'indicators' => $items->values(),
                ];
            })
            ->values();
    }

    /**
     * Ambil seluruh unit beserta daftar indikator tidak tercapai (dikelompokkan per Unit Kerja).
     */
    public static function getAllGroupedUnits(
        int|array $laporanIds,
        string $search = '',
        string $filterCategoryId = 'all',
        array $categoryIds = [],
        array $unitKerjaIds = []
    ): Collection {
        $rows = self::buildAggregatedBelowStandardQuery($laporanIds, $search, $filterCategoryId, $categoryIds, $unitKerjaIds)->get();

        return self::groupRowsByUnit($rows);
    }

    /**
     * Paginator daftar Unit Kerja yang memiliki indikator tidak tercapai.
     */
    public function getUnitGroupsProperty(): LengthAwarePaginator|Collection
    {
        $laporanIds = $this->periodContext['laporanIds'] ?? [];
        if (empty($laporanIds)) {
            return collect();
        }

        $allGroups = self::getAllGroupedUnits($laporanIds, $this->search, $this->filterCategoryId);

        if ($this->perPage === 'all' || (int) $this->perPage === -1) {
            return $allGroups;
        }

        $perPage = max(1, (int) $this->perPage);
        $currentPage = Paginator::resolveCurrentPage('belowPage');
        $sliced = $allGroups->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $sliced,
            $allGroups->count(),
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'belowPage',
            ]
        );
    }

    /**
     * Hitung ringkasan dari collection hasil query indikator tidak tercapai.
     *
     * @return array{total_units: int, total_indicators: int, by_category: Collection}
     */
    public static function summarizeRows(Collection $rows): array
    {
        $byCategory = $rows
            ->groupBy(fn ($row) => $row->category_id ?? 0)
            ->map(function (Collection $group, $catId) {
                $first = $group->first();

                return [
                    'id' => (int) $catId,
                    'label' => $first->category_short_name ?: ($first->category_name ?: 'Tanpa Kategori'),
                    'full_name' => $first->category_name ?: 'Tanpa Kategori',
                    'count' => $group->count(),
                    'color_var' => self::categoryColorVar((int) $catId),
                ];
            })
            ->sortByDesc('count')
            ->values();

        return [
            'total_units' => $rows->pluck('unit_kerja_id')->unique()->count(),
            'total_indicators' => $rows->count(),
            'by_category' => $byCategory,
        ];
    }

    /**
     * Ringkasan keseluruhan indikator tidak tercapai pada periode terpilih.
     */
    public static function getReportSummary(
        int|array $laporanIds,
        string $search = '',
        string $filterCategoryId = 'all',
        array $categoryIds = [],
        array $unitKerjaIds = []
    ): array {
        $rows = self::buildAggregatedBelowStandardQuery($laporanIds, $search, $filterCategoryId, $categoryIds, $unitKerjaIds)->get();

        return self::summarizeRows($rows);
    }

    public function getSummaryProperty(): array
    {
        $laporanIds = $this->periodContext['laporanIds'] ?? [];
        if (empty($laporanIds)) {
            return [
                'total_units' => 0,
                'total_indicators' => 0,
                'by_category' => collect(),
            ];
        }

        return self::getReportSummary($laporanIds);
    }

    public static function categoryColorVar(?int $categoryId): string
    {
        $palette = ['info', 'warning', 'primary', 'success', 'danger'];

        if (! $categoryId) {
            return 'gray';
        }

        return $palette[abs($categoryId) % count($palette)];
    }

    /**
     * Format nilai standar agar lebih mudah dibaca (simbol matematika + keterangan).
     *
     * @return array{badge: string, label: string}
     */
    public static function formatStandard(?string $operator, mixed $value): array
    {
        if ($value === null) {
            return ['badge' => '-', 'label' => '-'];
        }

        $formattedValue = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.').'%';

        return match ($operator) {
            '>=', '≥' => ['badge' => "≥ {$formattedValue}", 'label' => 'Minimal'],
            '<=', '≤' => ['badge' => "≤ {$formattedValue}", 'label' => 'Maksimal'],
            '=', '==' => ['badge' => "= {$formattedValue}", 'label' => 'Tepat'],
            '>' => ['badge' => "> {$formattedValue}", 'label' => 'Lebih dari'],
            '<' => ['badge' => "< {$formattedValue}", 'label' => 'Kurang dari'],
            default => ['badge' => trim(($operator ?? '')." {$formattedValue}"), 'label' => 'Target'],
        };
    }

    public function openExportModal(): void
    {
        $this->exportPeriodType = $this->periodType;
        $this->exportPeriod = $this->periodContext['selectedPeriod'] ?? $this->selectedPeriod;
        $this->exportSearch = $this->search;
        $this->exportCategoryIds = ($this->filterCategoryId !== 'all' && $this->filterCategoryId !== '')
            ? [(int) $this->filterCategoryId]
            : [];
        $this->exportUnitSearch = '';
        $this->showExportModal = true;
    }

    public function closeExportModal(): void
    {
        $this->showExportModal = false;
    }

    public function updatingExportPeriodType(string $value): void
    {
        $options = app(ImutPeriodService::class)->getAvailablePeriods($this->availableLaporans, $value);
        $this->exportPeriod = array_key_first($options) ?: null;
    }

    public function resetExportCategories(): void
    {
        $this->exportCategoryIds = [];
    }

    public function resetExportUnits(): void
    {
        $this->exportUnitKerjaIds = [];
        $this->exportUnitSearch = '';
    }

    public function getExportPeriodContextProperty(): array
    {
        $context = app(ImutPeriodService::class)->resolvePeriodContext(
            $this->availableLaporans,
            $this->exportPeriodType,
            $this->exportPeriod
        );

        if ($context['selectedPeriod'] && $this->exportPeriod !== $context['selectedPeriod']) {
            $this->exportPeriod = $context['selectedPeriod'];
        }

        return $context;
    }

    /**
     * Daftar nama bulan dalam periode terpilih untuk preview pada modal export.
     *
     * @return string[]
     */
    public function getExportMonthLabelsProperty(): array
    {
        $context = $this->exportPeriodContext;
        $selectedPeriod = $context['selectedPeriod'] ?? null;

        if (! $selectedPeriod) {
            return [];
        }

        $periodService = app(ImutPeriodService::class);
        $parsed = $periodService->parsePeriod($context['periodType'], $selectedPeriod);

        return $periodService->getMonthLabels($parsed['months']);
    }

    public function getAvailableUnitKerjasProperty(): Collection
    {
        $query = UnitKerja::select(['id', 'unit_name'])
            ->orderBy('unit_name');

        if (! empty(trim($this->exportUnitSearch))) {
            $query->where('unit_name', 'like', '%'.trim($this->exportUnitSearch).'%');
        }

        return $query->get();
    }

    public function getExportUrlProperty(): string
    {
        $context = $this->exportPeriodContext;
        $params = [
            'period_type' => $context['periodType'] ?? $this->exportPeriodType,
            'period' => $context['selectedPeriod'] ?? $this->exportPeriod,
            'merge_units' => $this->exportMergeUnits ? 1 : 0,
        ];

        $categoryIds = array_values(array_filter(array_map('intval', $this->exportCategoryIds)));
        if (! empty($categoryIds)) {
            $params['category_ids'] = $categoryIds;
        }

        $unitIds = array_values(array_filter(array_map('intval', $this->exportUnitKerjaIds)));
        if (! empty($unitIds)) {
            $params['unit_kerja_ids'] = $unitIds;
        }

        if (trim($this->exportSearch) !== '') {
            $params['search'] = trim($this->exportSearch);
        }

        return route('export.imut-tidak-tercapai', $params);
    }
}
