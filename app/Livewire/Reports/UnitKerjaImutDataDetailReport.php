<?php

namespace App\Livewire\Reports;

use App\Filament\Exports\SummaryUnitKerjaReportDetailExport;
use App\Models\ImutCategory;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
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

    public function updateReport(int $laporanId, int $unitKerjaId): void
    {
        $this->laporanId = $laporanId;
        $this->unitKerjaId = $unitKerjaId;
        $this->loadLaporan();
        $this->dispatch('$refresh');
    }

    protected function loadLaporan(): void
    {
        if ($this->laporanId) {
            $this->laporan = LaporanImut::find($this->laporanId);
        }
    }

    public function refreshTable(): void
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        return $table
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
        return fn() => LaporanUnitKerja::getReportByUnitKerjaDetails($this->laporanId, $this->unitKerjaId);
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

            TextColumn::make('numerator_value')
                ->label('N')
                ->alignCenter()
                ->toggleable()
                ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                ->summarize(
                    Summarizer::make()
                        ->label('Total N')
                        ->using(fn(Builder $query) => number_format($query->sum('numerator_value'), 2))
                ),

            TextColumn::make('denominator_value')
                ->label('D')
                ->alignCenter()
                ->toggleable()
                ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                ->summarize(
                    Summarizer::make()
                        ->label('Total D')
                        ->using(fn(Builder $query) => number_format($query->sum('denominator_value'), 2))
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

    protected function buildPerhitunganSchemaForAction($livewireComponent): array
    {
        $shouldLock = $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian');

        return [
            TextInput::make('numerator_value')
                ->label('Numerator')
                ->numeric()
                ->placeholder('0.00')
                ->nullable()
                ->debounce(1000)
                ->readOnly()
                ->afterStateUpdated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),

            TextInput::make('denominator_value')
                ->label('Denominator')
                ->numeric()
                ->placeholder('0.00')
                ->nullable()
                ->debounce(1000)
                ->readOnly()
                ->afterStateUpdated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),

            TextInput::make('result_operation')
                ->label('Result (%)')
                ->numeric()
                ->placeholder('0.00')
                ->readOnly()
                ->debounce(1000)
                ->dehydrated(false)
                ->afterStateHydrated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),
        ];
    }

    protected function getMediaUploadFieldForAction($livewireComponent): array
    {
        $shouldLock = $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian');

        return [
            SpatieMediaLibraryFileUpload::make('document_upload')
                ->label('Unggah Dokumen Pendukung')
                ->collection(fn(callable $get) => $get('selected_collection'))
                ->directory(fn(callable $get) => 'uploads/imut-documents/' . ($get('selected_collection')))
                ->openable()
                ->downloadable()
                ->maxSize(20480)
                ->preserveFilenames()
                ->previewable(true)
                ->columnSpanFull()
                ->disabled($shouldLock)
                ->acceptedFileTypes([
                    'application/pdf',
                    'image/*',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->helperText('File yang didukung: PDF, Word, Excel, Gambar. Maks. 20MB')
        ];
    }

    public function isLaporanPeriodClosed(): bool
    {
        return ! $this->isLaporanEditable();
    }

    public function isLaporanEditable(): bool
    {
        if (! $this->laporan) {
            $this->loadLaporan();
        }

        if (! $this->laporan) {
            return false;
        }

        $today = Carbon::today();
        $start = Carbon::parse($this->laporan->assessment_period_start);
        $end = Carbon::parse($this->laporan->assessment_period_end);

        return $today->betweenIncluded($start, $end);
    }

    public function openTableView(): void
    {
        if (!$this->laporanId || !$this->unitKerjaId) {
            return;
        }

        // Get first imut penilaian dengan relasi profile
        $penilaian = ImutPenilaian::query()
            ->whereHas('laporanUnitKerja', function ($query) {
                $query->where('laporan_imut_id', $this->laporanId)
                    ->where('unit_kerja_id', $this->unitKerjaId);
            })
            ->with(['profile.formTemplates', 'laporanUnitKerja.laporanImut'])
            ->first();

        if (!$penilaian || !$penilaian->profile) {
            return;
        }

        // Get form template dari profile
        $formTemplate = $penilaian->profile->formTemplates()->first();
        if (!$formTemplate) {
            return;
        }

        // Build period dari laporan
        $laporan = $penilaian->laporanUnitKerja->laporanImut;
        if (!$laporan) {
            return;
        }

        $period = sprintf('%04d-%02d', $laporan->report_year, $laporan->report_month);

        // Build URL dengan parameter yang aman
        $params = [
            'form_template_id' => $formTemplate->id,
            'imut_profile_id' => $penilaian->profile->id,
            'unit_kerja_id' => $this->unitKerjaId,
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
