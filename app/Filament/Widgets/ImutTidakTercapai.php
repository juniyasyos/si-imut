<?php

namespace App\Filament\Widgets;

use App\Facades\LaporanImut as LaporanImutFacade;
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

    public string $filterIndicatorType = 'all';

    public string|int $perPage = 10;

    protected $queryString = [
        'selectedLaporanId' => ['except' => null],
        'search' => ['except' => ''],
        'filterIndicatorType' => ['except' => 'all'],
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutTidakTercapai') ?? false;
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

    public function updatingFilterIndicatorType(): void
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
        $this->filterIndicatorType = 'all';
        $this->resetPage('belowPage');
    }

    public function getAvailableLaporansProperty(): Collection
    {
        return LaporanImut::select(['id', 'name', 'report_month', 'report_year', 'assessment_period_start', 'assessment_period_end', 'status'])
            ->orderByDesc('assessment_period_start')
            ->get();
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
    protected function getBelowStandardBaseQuery(int $laporanId, bool $applyFilters = true): Builder
    {
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

        if ($applyFilters) {
            if (! empty(trim($this->search))) {
                $searchTerm = '%' . trim($this->search) . '%';
                $query->where(function (Builder $q) use ($searchTerm) {
                    $q->where('unit_kerja.unit_name', 'like', $searchTerm)
                        ->orWhere('imut_data.title', 'like', $searchTerm)
                        ->orWhere('imut_kategori.short_name', 'like', $searchTerm);
                });
            }

            if ($this->filterIndicatorType !== 'all') {
                $query->where('imut_profil.indicator_type', $this->filterIndicatorType);
            }
        }

        return $query;
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

        $unitQuery = $this->getBelowStandardBaseQuery($laporan->id)
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

        if ($this->perPage === 'all' || (int) $this->perPage === -1) {
            $units = $unitQuery->get();
        } else {
            $units = $unitQuery->paginate((int) $this->perPage, ['*'], 'belowPage');
        }

        $unitCollection = $units instanceof LengthAwarePaginator ? $units->getCollection() : $units;
        $laporanUnitKerjaIds = $unitCollection->pluck('laporan_unit_kerja_id')->all();

        if (empty($laporanUnitKerjaIds)) {
            return $units;
        }

        $percentageSql = ImutCalculationService::percentageExpression(
            'imut_penilaians.numerator_value',
            'imut_penilaians.denominator_value',
            'capaian_percentage'
        );

        $indicatorsByUnit = $this->getBelowStandardBaseQuery($laporan->id)
            ->whereIn('laporan_unit_kerjas.id', $laporanUnitKerjaIds)
            ->select([
                'imut_penilaians.id',
                'imut_penilaians.laporan_unit_kerja_id',
                'imut_penilaians.numerator_value',
                'imut_penilaians.denominator_value',
                'imut_data.title as indicator_title',
                'imut_kategori.short_name as category_short_name',
                'imut_profil.indicator_type',
                'imut_profil.target_operator',
                'imut_profil.target_value',
                DB::raw($percentageSql),
            ])
            ->orderBy('capaian_percentage')
            ->orderBy('imut_data.title')
            ->get()
            ->groupBy('laporan_unit_kerja_id');

        $unitCollection->transform(function ($unit) use ($indicatorsByUnit) {
            $unit->indicators = $indicatorsByUnit->get($unit->laporan_unit_kerja_id, collect());

            return $unit;
        });

        return $units;
    }

    /**
     * Ringkasan keseluruhan indikator tidak tercapai pada laporan terpilih.
     */
    public function getSummaryProperty(): array
    {
        $laporan = $this->getLaporan();
        if (! $laporan) {
            return [
                'total_units' => 0,
                'total_indicators' => 0,
                'process_count' => 0,
                'output_count' => 0,
                'outcome_count' => 0,
            ];
        }

        $rows = $this->getBelowStandardBaseQuery($laporan->id, applyFilters: false)
            ->select([
                'laporan_unit_kerjas.unit_kerja_id',
                'imut_profil.indicator_type',
            ])
            ->get();

        return [
            'total_units' => $rows->pluck('unit_kerja_id')->unique()->count(),
            'total_indicators' => $rows->count(),
            'process_count' => $rows->where('indicator_type', 'process')->count(),
            'output_count' => $rows->where('indicator_type', 'output')->count(),
            'outcome_count' => $rows->where('indicator_type', 'outcome')->count(),
        ];
    }

    public static function formatIndicatorType(?string $type): string
    {
        return match ($type) {
            'process' => 'Proses',
            'output' => 'Output',
            'outcome' => 'Outcome',
            default => 'Lainnya',
        };
    }

    public static function indicatorTypeVar(?string $type): string
    {
        return match ($type) {
            'process' => 'warning',
            'output' => 'info',
            'outcome' => 'success',
            default => 'gray',
        };
    }
}
