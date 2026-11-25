<?php

namespace App\Filament\Widgets;

use App\Facades\LaporanImut as LaporanImutFacade;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Modules\ImutMaster\Models\ImutCategory;
use App\Repositories\Interfaces\LaporanRepositoryInterface;
use App\Services\Chart\ImutPeriodService;
use App\Services\Core\ImutCalculationService;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ImutTercapai extends Widget
{
    use WithPagination;

    protected static string $view = 'filament.widgets.imut-tercapai-widget';

    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    public string $periodType = 'month';

    public ?string $selectedPeriod = null;

    public string $search = '';

    public string $filterLevel = 'all';

    public string $sortColumn = 'standard_percentage';

    public string $sortDirection = 'desc';

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

    public string $exportFilterLevel = 'all';

    public string $exportSort = 'percentage_desc';

    protected $queryString = [
        'periodType' => ['except' => 'month'],
        'selectedPeriod' => ['except' => null],
        'search' => ['except' => ''],
        'filterLevel' => ['except' => 'all'],
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
        $this->resetPage();
    }

    public function updatingSelectedPeriod(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterLevel(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->search = '';
        $this->filterLevel = 'all';
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = in_array($column, ['unit_name']) ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    public function getAvailableLaporansProperty(): Collection
    {
        return LaporanImut::select(['id', 'name', 'report_month', 'report_year', 'assessment_period_start', 'assessment_period_end', 'status'])
            ->orderByDesc('assessment_period_start')
            ->get();
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
        return $this->periodContext['primaryLaporan'] ?? LaporanImutFacade::getLatestLaporan();
    }

    protected function getFilteredQuery(array $laporanIds): ?Builder
    {
        if (empty($laporanIds)) {
            return null;
        }

        $laporanRepository = app(LaporanRepositoryInterface::class);
        $query = $laporanRepository->getReportByUnitKerja($laporanIds);

        // Filter search unit name
        if (! empty(trim($this->search))) {
            $searchTerm = '%'.trim($this->search).'%';
            $query->where('unit_kerja.unit_name', 'like', $searchTerm);
        }

        // Filter status capaian
        $meetsStandardCondition = ImutCalculationService::meetsStandardExpression(
            ImutCalculationService::percentageExpression('imut_penilaians.numerator_value', 'imut_penilaians.denominator_value'),
            'imut_profil.target_value',
            'imut_profil.target_operator'
        );
        $meetsStandardExpr = "SUM(CASE WHEN imut_penilaians.numerator_value IS NOT NULL AND imut_penilaians.denominator_value IS NOT NULL AND imut_penilaians.denominator_value != 0 AND {$meetsStandardCondition} THEN 1 ELSE 0 END)";
        $belowStandardExpr = "SUM(CASE WHEN imut_penilaians.numerator_value IS NOT NULL AND imut_penilaians.denominator_value IS NOT NULL AND imut_penilaians.denominator_value != 0 AND NOT {$meetsStandardCondition} THEN 1 ELSE 0 END)";
        $emptyExpr = 'SUM(CASE WHEN imut_penilaians.numerator_value IS NULL OR imut_penilaians.denominator_value IS NULL OR imut_penilaians.denominator_value = 0 THEN 1 ELSE 0 END)';

        match ($this->filterLevel) {
            'high' => $query->havingRaw("({$meetsStandardExpr} / NULLIF(COUNT(imut_penilaians.id), 0)) >= 0.8"),
            'medium' => $query->havingRaw("({$meetsStandardExpr} / NULLIF(COUNT(imut_penilaians.id), 0)) >= 0.5 AND ({$meetsStandardExpr} / NULLIF(COUNT(imut_penilaians.id), 0)) < 0.8"),
            'low' => $query->havingRaw("({$meetsStandardExpr} / NULLIF(COUNT(imut_penilaians.id), 0)) < 0.5"),
            'has_below' => $query->havingRaw("{$belowStandardExpr} > 0"),
            'has_empty' => $query->havingRaw("{$emptyExpr} > 0"),
            'all_standard' => $query->havingRaw("{$meetsStandardExpr} = COUNT(imut_penilaians.id) AND COUNT(imut_penilaians.id) > 0"),
            default => null,
        };

        // Sorting
        $allowedSorts = ['unit_name', 'total_count', 'meets_standard_count', 'below_standard_count', 'empty_count', 'standard_percentage'];
        $column = in_array($this->sortColumn, $allowedSorts) ? $this->sortColumn : 'standard_percentage';
        $direction = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';

        if ($column === 'standard_percentage') {
            $query->orderByRaw("({$meetsStandardExpr} / NULLIF(COUNT(imut_penilaians.id), 0)) {$direction}");
        } else {
            $query->orderBy($column, $direction);
        }

        return $query;
    }

    public function getRecordsProperty()
    {
        $laporanIds = $this->periodContext['laporanIds'] ?? [];
        if (empty($laporanIds)) {
            return collect();
        }

        $query = $this->getFilteredQuery($laporanIds);
        if (! $query) {
            return collect();
        }

        if ($this->perPage === 'all' || (int) $this->perPage === -1) {
            return $query->get();
        }

        return $query->paginate((int) $this->perPage);
    }

    public static function getSummaryFromRecords(Collection $records): array
    {
        $totalUnits = $records->count();
        $totalIndicators = (int) $records->sum('total_count');
        $totalStandard = (int) $records->sum('meets_standard_count');
        $totalBelow = (int) $records->sum('below_standard_count');
        $totalEmpty = (int) $records->sum('empty_count');
        $overallPercentage = $totalIndicators > 0 ? round(($totalStandard / $totalIndicators) * 100, 2) : 0;

        return [
            'total_units' => $totalUnits,
            'total_indicators' => $totalIndicators,
            'total_standard' => $totalStandard,
            'total_below' => $totalBelow,
            'total_empty' => $totalEmpty,
            'overall_percentage' => $overallPercentage,
        ];
    }

    public function getSummaryProperty(): array
    {
        $laporanIds = $this->periodContext['laporanIds'] ?? [];
        if (empty($laporanIds)) {
            return self::getSummaryFromRecords(collect());
        }

        $laporanRepository = app(LaporanRepositoryInterface::class);
        $records = $laporanRepository->getReportByUnitKerja($laporanIds)->get();

        return self::getSummaryFromRecords($records);
    }

    public function openExportModal(): void
    {
        $this->exportPeriodType = $this->periodType;
        $this->exportPeriod = $this->periodContext['selectedPeriod'] ?? $this->selectedPeriod;
        $this->exportFilterLevel = $this->filterLevel;
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
     * Daftar nama bulan yang akan menjadi kolom Excel berdasarkan jenjang & periode terpilih.
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

    public function getAvailableCategoriesProperty(): Collection
    {
        return ImutCategory::select(['id', 'short_name', 'category_name'])
            ->orderBy('category_name')
            ->get();
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
            'filter_level' => $this->exportFilterLevel,
            'sort' => $this->exportSort,
        ];

        $categoryIds = array_values(array_filter(array_map('intval', $this->exportCategoryIds)));
        if (! empty($categoryIds)) {
            $params['category_ids'] = $categoryIds;
        }

        $unitIds = array_values(array_filter(array_map('intval', $this->exportUnitKerjaIds)));
        if (! empty($unitIds)) {
            $params['unit_kerja_ids'] = $unitIds;
        }

        return route('export.unit-kerja-rekap', $params);
    }
}
