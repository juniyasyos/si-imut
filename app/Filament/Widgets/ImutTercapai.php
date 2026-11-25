<?php

namespace App\Filament\Widgets;

use App\Facades\LaporanImut as LaporanImutFacade;
use App\Models\LaporanImut;
use App\Repositories\Interfaces\LaporanRepositoryInterface;
use App\Services\Core\ImutCalculationService;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ImutTercapai extends Widget
{
    use WithPagination;

    protected static string $view = 'filament.widgets.imut-tercapai-widget';
    protected static ?int $sort = 6;
    protected static bool $isLazy = true;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    public string $search = '';
    public string $filterLevel = 'all';
    public string $sortColumn = 'standard_percentage';
    public string $sortDirection = 'desc';
    public string|int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterLevel' => ['except' => 'all'],
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutTercapai') ?? false;
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

    public function getLaporan(): ?LaporanImut
    {
        $laporan = LaporanImutFacade::getLatestLaporan();
        if ($laporan && (! $laporan->name || ! $laporan->assessment_period_end)) {
            $laporan = LaporanImut::find($laporan->id);
        }

        return $laporan;
    }

    protected function getFilteredQuery(?int $laporanId): ?Builder
    {
        if (! $laporanId) {
            return null;
        }

        $laporanRepository = app(LaporanRepositoryInterface::class);
        $query = $laporanRepository->getReportByUnitKerja($laporanId);

        // Filter search unit name
        if (! empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
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
        $emptyExpr = "SUM(CASE WHEN imut_penilaians.numerator_value IS NULL OR imut_penilaians.denominator_value IS NULL OR imut_penilaians.denominator_value = 0 THEN 1 ELSE 0 END)";

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
        $laporan = $this->getLaporan();
        if (! $laporan) {
            return collect();
        }

        $query = $this->getFilteredQuery($laporan->id);
        if (! $query) {
            return collect();
        }

        if ($this->perPage === 'all' || (int) $this->perPage === -1) {
            return $query->get();
        }

        return $query->paginate((int) $this->perPage);
    }

    public function getSummaryProperty(): array
    {
        $laporan = $this->getLaporan();
        if (! $laporan) {
            return [
                'total_units' => 0,
                'total_indicators' => 0,
                'total_standard' => 0,
                'total_below' => 0,
                'total_empty' => 0,
                'overall_percentage' => 0,
            ];
        }

        $laporanRepository = app(LaporanRepositoryInterface::class);
        $records = $laporanRepository->getReportByUnitKerja($laporan->id)->get();

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
}
