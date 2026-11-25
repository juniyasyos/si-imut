<?php

namespace App\Filament\Widgets;

use App\Facades\LaporanImut as LaporanImutFacade;
use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Services\Core\ImutCalculationService;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public ?int $selectedLaporanId = null;

    public string $search = '';

    public string $filterCategoryId = 'all';

    public string|int $perPage = 10;

    protected $queryString = [
        'selectedLaporanId' => ['except' => null],
        'search' => ['except' => ''],
        'filterCategoryId' => ['except' => 'all'],
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutTercapai') ?? false;
    }

    public function mount(): void
    {
        if (! $this->selectedLaporanId) {
            $latest = LaporanImutFacade::getLatestLaporan();
            $this->selectedLaporanId = $latest?->id;
        }
    }

    public function updatingSelectedLaporanId(): void
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

    public function getLaporan(): ?LaporanImut
    {
        if ($this->selectedLaporanId) {
            $laporan = LaporanImut::find($this->selectedLaporanId);
            if ($laporan) {
                return $laporan;
            }
        }

        $laporan = LaporanImutFacade::getLatestLaporan();
        if ($laporan && (! $laporan->name || ! $laporan->assessment_period_end)) {
            $laporan = LaporanImut::find($laporan->id);
        }

        if ($laporan && ! $this->selectedLaporanId) {
            $this->selectedLaporanId = $laporan->id;
        }

        return $laporan;
    }

    /**
     * Query dasar untuk seluruh baris penilaian indikator yang tidak memenuhi standar.
     */
    public static function buildBelowStandardBaseQuery(
        int $laporanId,
        string $search = '',
        string $filterCategoryId = 'all'
    ): Builder {
        $percentageSql = ImutCalculationService::percentageExpression(
            'imut_penilaians.numerator_value',
            'imut_penilaians.denominator_value',
            'capaian_percentage'
        );

        $meetsStandardCondition = ImutCalculationService::meetsStandardExpression(
            $percentageSql,
            'imut_profil.target_value',
            'imut_profil.target_operator'
        );

        $query = LaporanUnitKerja::query()
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->leftJoin('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->whereNotNull('imut_penilaians.numerator_value')
            ->whereNotNull('imut_penilaians.denominator_value')
            ->where('imut_penilaians.denominator_value', '!=', 0)
            ->whereRaw("NOT {$meetsStandardCondition}");

        if (! empty(trim($search))) {
            $searchTerm = '%' . trim($search) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('unit_kerja.unit_name', 'like', $searchTerm)
                    ->orWhere('imut_data.title', 'like', $searchTerm)
                    ->orWhere('imut_kategori.short_name', 'like', $searchTerm)
                    ->orWhere('imut_kategori.category_name', 'like', $searchTerm);
            });
        }

        if ($filterCategoryId !== 'all' && $filterCategoryId !== '') {
            $query->where('imut_data.imut_kategori_id', (int) $filterCategoryId);
        }

        return $query;
    }

    /**
     * Query builder daftar Unit Kerja yang memiliki indikator tidak tercapai.
     */
    protected static function buildUnitQuery(
        int $laporanId,
        string $search = '',
        string $filterCategoryId = 'all'
    ): Builder {
        return self::buildBelowStandardBaseQuery($laporanId, $search, $filterCategoryId)
            ->select([
                'laporan_unit_kerjas.id as laporan_unit_kerja_id',
                'laporan_unit_kerjas.laporan_imut_id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                DB::raw('COUNT(imut_penilaians.id) as below_count'),
            ])
            ->groupBy(
                'laporan_unit_kerjas.id',
                'laporan_unit_kerjas.laporan_imut_id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name'
            )
            ->orderBy('unit_kerja.unit_name');
    }

    /**
     * Attach koleksi indikator tidak tercapai ke masing-masing Unit Kerja.
     */
    protected static function hydrateUnitIndicators(
        Collection $unitCollection,
        int $laporanId,
        string $search = '',
        string $filterCategoryId = 'all'
    ): Collection {
        $laporanUnitKerjaIds = $unitCollection->pluck('laporan_unit_kerja_id')->all();

        if (empty($laporanUnitKerjaIds)) {
            return $unitCollection;
        }

        $percentageSql = ImutCalculationService::percentageExpression(
            'imut_penilaians.numerator_value',
            'imut_penilaians.denominator_value',
            'capaian_percentage'
        );

        $indicatorsByUnit = self::buildBelowStandardBaseQuery($laporanId, $search, $filterCategoryId)
            ->whereIn('laporan_unit_kerjas.id', $laporanUnitKerjaIds)
            ->select([
                'imut_penilaians.id',
                'imut_penilaians.laporan_unit_kerja_id',
                'imut_penilaians.numerator_value',
                'imut_penilaians.denominator_value',
                'imut_data.title as indicator_title',
                'imut_kategori.id as category_id',
                'imut_kategori.short_name as category_short_name',
                'imut_kategori.category_name as category_name',
                'imut_profil.target_operator',
                'imut_profil.target_value',
                DB::raw($percentageSql),
            ])
            ->orderBy('capaian_percentage')
            ->orderBy('imut_data.title')
            ->get()
            ->groupBy('laporan_unit_kerja_id');

        return $unitCollection->transform(function ($unit) use ($indicatorsByUnit) {
            $unit->indicators = $indicatorsByUnit->get($unit->laporan_unit_kerja_id, collect());

            return $unit;
        });
    }

    /**
     * Ambil seluruh unit beserta daftar indikator tidak tercapai (untuk cetak laporan).
     */
    public static function getAllGroupedUnits(
        int $laporanId,
        string $search = '',
        string $filterCategoryId = 'all'
    ): Collection {
        $units = self::buildUnitQuery($laporanId, $search, $filterCategoryId)->get();

        return self::hydrateUnitIndicators($units, $laporanId, $search, $filterCategoryId);
    }

    /**
     * Paginator daftar Unit Kerja yang memiliki indikator tidak tercapai.
     */
    public function getUnitGroupsProperty(): LengthAwarePaginator|Collection
    {
        $laporan = $this->getLaporan();
        if (! $laporan) {
            return collect();
        }

        $unitQuery = self::buildUnitQuery($laporan->id, $this->search, $this->filterCategoryId);

        if ($this->perPage === 'all' || (int) $this->perPage === -1) {
            $units = $unitQuery->get();
            self::hydrateUnitIndicators($units, $laporan->id, $this->search, $this->filterCategoryId);

            return $units;
        }

        $units = $unitQuery->paginate((int) $this->perPage, ['*'], 'belowPage');
        self::hydrateUnitIndicators($units->getCollection(), $laporan->id, $this->search, $this->filterCategoryId);

        return $units;
    }

    /**
     * Ringkasan keseluruhan indikator tidak tercapai pada laporan terpilih.
     */
    public static function getReportSummary(
        int $laporanId,
        string $search = '',
        string $filterCategoryId = 'all'
    ): array {
        $rows = self::buildBelowStandardBaseQuery($laporanId, $search, $filterCategoryId)
            ->select([
                'laporan_unit_kerjas.unit_kerja_id',
                'imut_kategori.id as category_id',
                'imut_kategori.short_name as category_short_name',
                'imut_kategori.category_name as category_name',
            ])
            ->get();

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

    public function getSummaryProperty(): array
    {
        $laporan = $this->getLaporan();
        if (! $laporan) {
            return [
                'total_units' => 0,
                'total_indicators' => 0,
                'by_category' => collect(),
            ];
        }

        return self::getReportSummary($laporan->id);
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

        $formattedValue = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . '%';

        return match ($operator) {
            '>=', '≥' => ['badge' => "≥ {$formattedValue}", 'label' => 'Minimal'],
            '<=', '≤' => ['badge' => "≤ {$formattedValue}", 'label' => 'Maksimal'],
            '=', '==' => ['badge' => "= {$formattedValue}", 'label' => 'Tepat'],
            '>' => ['badge' => "> {$formattedValue}", 'label' => 'Lebih dari'],
            '<' => ['badge' => "< {$formattedValue}", 'label' => 'Kurang dari'],
            default => ['badge' => trim(($operator ?? '') . " {$formattedValue}"), 'label' => 'Target'],
        };
    }
}
