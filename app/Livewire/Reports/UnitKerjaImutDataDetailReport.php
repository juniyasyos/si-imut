<?php

namespace App\Livewire\Reports;

use App\Filament\Exports\SummaryUnitKerjaReportDetailExport;
use App\Models\ImutCategory;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Services\Reporting\ImutReportService;
use App\Models\UnitKerja;
use App\Traits\HasPercentageColor;
use App\Traits\HasTableHelpers;
use App\Filament\Traits\ReportDetailAction\BuildIsiPenilaian;
use App\Filament\Traits\ReportDetailAction\DetailInfoReport;
use Carbon\Carbon;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Livewire\Component;

class UnitKerjaImutDataDetailReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPercentageColor;
    use HasTableHelpers;
    use BuildIsiPenilaian;
    use DetailInfoReport;

    public ?int $laporanId = null;

    public ?int $unitKerjaId = null;

    protected ?LaporanImut $laporan = null;

    protected $listeners = [
        'report-changed' => 'updateReport',
        'refreshTable' => '$refresh',
    ];

    /**
     * On mount, load laporan (if provided) and check URL for auto-open action parameters.
     * Supported URL query params:
     * - action (or open_action): name of the table action to open (e.g. "isi_penilaian" or "detail_info")
     * - record: explicit imut_penilaians.id
     * - imut_profile_id: will be resolved to the matching ImutPenilaian for the current laporan/unit
     * - form_template_id: will be resolved to the ImutProfile then to ImutPenilaian
     */
    public function mount(): void
    {
        // Ensure laporan is loaded when component mounted with props
        $this->loadLaporan();

        $action = request()->query('action') ?? request()->query('open_action');
        $recordId = request()->query('record');
        $imutProfileId = request()->query('imut_profile_id');
        $formTemplateId = request()->query('form_template_id');

        // Resolve record id from imut_profile_id when explicit record not provided
        if (!$recordId && $imutProfileId) {
            $penilaian = \App\Models\ImutPenilaian::where('imut_profil_id', (int) $imutProfileId)
                ->whereHas('laporanUnitKerja', function ($q) {
                    if ($this->laporanId) {
                        $q->where('laporan_imut_id', $this->laporanId);
                    }
                    if ($this->unitKerjaId) {
                        $q->where('unit_kerja_id', $this->unitKerjaId);
                    }
                })->first();

            $recordId = $penilaian?->id;
        }

        // Resolve record id from form_template_id -> imut_profile -> penilaian
        if (!$recordId && $formTemplateId) {
            $profile = \App\Models\ImutProfile::whereHas('formTemplates', function ($q) use ($formTemplateId) {
                $q->where('id', (int) $formTemplateId);
            })->first();

            if ($profile) {
                $penilaian = \App\Models\ImutPenilaian::where('imut_profil_id', $profile->id)
                    ->whereHas('laporanUnitKerja', function ($q) {
                        if ($this->laporanId) {
                            $q->where('laporan_imut_id', $this->laporanId);
                        }
                        if ($this->unitKerjaId) {
                            $q->where('unit_kerja_id', $this->unitKerjaId);
                        }
                    })->first();

                $recordId = $penilaian?->id;
            }
        }

        if ($action && $recordId) {
            $mounted = false;

            // Prefer Filament/Livewire server-side mount if component supports it
            if (method_exists($this, 'mountTableAction')) {
                try {
                    // mountTableAction is provided by Filament Tables (InteractsWithTable)
                    $this->mountTableAction($action, (int) $recordId);
                    $mounted = true;
                } catch (\Throwable $e) {
                    // log and fallback to client-side trigger
                    \Illuminate\Support\Facades\Log::warning('mountTableAction failed', ['action' => $action, 'record' => $recordId, 'exception' => $e->getMessage()]);
                }
            }

            // If server-side mount did not run, dispatch client-side fallback
            if (!$mounted) {
                $this->dispatchBrowserEvent('open-table-action', ['action' => $action, 'recordId' => (int) $recordId]);

                // keep URL intact so user/dev can retry — do not remove query params here
                return;
            }

            // Only remove the URL keys after successful server-side mount
            $this->dispatchBrowserEvent('replaceUrlQuery', [
                'keys' => ['action', 'open_action', 'record'],
            ]);
        }
    }

    public function updateReport(int $laporanId, int $unitKerjaId): void
    {
        $this->laporanId = $laporanId;
        $this->unitKerjaId = $unitKerjaId;
        $this->loadLaporan();
        $this->dispatch('$refresh');
    }

    /**
     * Load laporan data.
     *
     * @return bool True if laporan was successfully loaded
     */
    protected function loadLaporan(): bool
    {
        if ($this->laporanId) {
            $this->laporan = LaporanImut::find($this->laporanId);
            return $this->laporan !== null;
        }

        return false;
    }

    public function refreshTable(): void
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->query($this->getTableQuery())
            ->columns($this->getTableColumnsArray())
            ->filters($this->getTableFiltersArray())
            ->headerActions($this->getTableHeaderActionsArray())
            ->actions($this->getTableActionsArray())
            ->recordAction('isi_penilaian')
            ->bulkActions([]);
    }

    protected function getTableQuery()
    {
        $reportService = app(ImutReportService::class);
        return fn() => $reportService->getUnitKerjaDetailData($this->laporanId, $this->unitKerjaId);
    }

    /**
     * Get the table columns array.
     */
    protected function getTableColumnsArray(): array
    {
        return [
            TextColumn::make('imut_data')
                ->label('Imut Data')
                ->wrap()
                ->lineClamp(2)
                ->searchable(query: fn(EloquentBuilder $query, string $search) => $query->where('imut_data.title', 'like', "%{$search}%")),

            TextColumn::make('imut_kategori')
                ->label('Imut Kategori')
                ->toggleable()
                ->sortable()
                ->color(fn($record) => $this->getCategoryColor($record->imut_kategori_id))
                ->toggleable(isToggledHiddenByDefault: false)
                ->badge(),

            TextColumn::make('imut_profil')
                ->label('Imut Profil')
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),

            TextColumn::make('is_monthly')
                ->label('Tipe Pengisian Indikator')
                ->badge()
                ->icon('heroicon-o-calendar')
                ->formatStateUsing(
                    fn(bool $state) =>
                    $state ? 'Harian' : 'Bulanan'
                )
                ->color(
                    fn(bool $state) =>
                    $state ? 'info' : 'success'
                )
                ->tooltip(
                    fn($record) =>
                    $record->is_monthly
                    ? 'Pengisian dilakukan 1 kali setiap bulan'
                    : 'Pengisian dilakukan setiap hari'
                )
                ->alignCenter()
                ->sortable(),

            TextColumn::make('numerator_value')
                ->label('N')
                ->alignCenter()
                ->toggleable()
                ->formatStateUsing(fn($state) => (int) $state)
                ->summarize(
                    Summarizer::make()
                        ->label('Total N')
                        ->using(fn(Builder $query) => (int) $query->sum('numerator_value'))
                ),

            TextColumn::make('denominator_value')
                ->label('D')
                ->alignCenter()
                ->toggleable()
                ->formatStateUsing(fn($state) => (int) $state)
                ->summarize(
                    Summarizer::make()
                        ->label('Total D')
                        ->using(fn(Builder $query) => (int) $query->sum('denominator_value'))
                ),

            TextColumn::make('percentage')
                ->label('Persentase (%)')
                ->alignCenter()
                ->toggleable()
                ->suffix('%')
                ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                ->color(fn($record) => $this->getPercentageColor($record))
                ->summarize(
                    Summarizer::make()
                        ->label('Total Persentase')
                        ->using(function (Builder $query) {
                            $n = $query->sum('numerator_value');
                            $d = $query->sum('denominator_value');

                            return $d > 0 ? round(($n / $d) * 100, 2) : 0;
                        })
                        ->suffix('%')
                ),

            TextColumn::make('imut_standard')
                ->label('Standar Indikator')
                ->suffix('%')
                ->toggleable()
                ->color('info')
                ->badge()
                ->alignCenter(),
        ];
    }

    /**
     * Get the table filters array.
     */
    protected function getTableFiltersArray(): array
    {
        return [
            SelectFilter::make('imut_kategori')
                ->label('Imut Kategori')
                ->options(fn() => ImutCategory::pluck('short_name', 'id')->toArray())
                ->attribute('imut_kategori_id')
                ->multiple()
                ->placeholder('Semua Kategori'),
        ];
    }

    /**
     * Get the table header actions array.
     */
    protected function getTableHeaderActionsArray(): array
    {
        return [
            ExportAction::make()
                ->exporter(SummaryUnitKerjaReportDetailExport::class)
                ->label(fn() => 'Export Laporan ' . UnitKerja::where('id', $this->unitKerjaId)->value('unit_name'))
                ->color('gray'),
        ];
    }

    /**
     * Get the table actions array.
     */
    protected function getTableActionsArray(): array
    {
        return [
            $this->buildDetailInfo(),
            $this->buildIsiPenilaianAction(),
        ];
    }

    public function openTableView($formTemplateId, $imutProfileId, $unitKerjaId, $period, $laporanId): void
    {
        // Build URL dengan parameter yang aman
        $params = [
            'form_template_id' => $formTemplateId,
            'imut_profile_id' => $imutProfileId,
            'unit_kerja_id' => $unitKerjaId,
            'period' => $period,
        ];

        $url = route('table-view') . '?' . http_build_query($params);
        $this->dispatch('openUrlInNewTab', $url);
    }

    public function render()
    {
        return view('livewire.reports.unit-kerja-imut-data-detail-report');
    }
}
