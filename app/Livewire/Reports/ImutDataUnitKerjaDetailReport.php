<?php

namespace App\Livewire\Reports;

use App\Filament\Exports\SummaryUnitKerjaReportDetailExport;
use App\Filament\Resources\ImutPenilaianResource\Schema\ImutPenilaianResourceSchema;
use App\Models\ImutCategory;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Services\Reporting\ImutReportService;
use App\Traits\HasPercentageColor;
use App\Traits\HasTableHelpers;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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

class ImutDataUnitKerjaDetailReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPercentageColor;
    use HasTableHelpers;

    public ?int $laporanId = null;

    public ?int $imutDataId = null;

    protected ?LaporanImut $laporan = null;

    protected $listeners = [
        'report-changed' => 'updateReport',
        'refreshTable' => '$refresh',
    ];

    public function updateReport(int $laporanId, int $imutDataId): void
    {
        $this->laporanId = $laporanId;
        $this->imutDataId = $imutDataId;
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
        $reportService = app(ImutReportService::class);
        
        return $table
            ->query(fn() => $reportService->getImutDataDetailData($this->laporanId, $this->imutDataId))
            ->columns([
                TextColumn::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->wrap()
                    ->lineClamp(2)
                    ->sortable()
                    ->weight('medium')
                    ->searchable(query: function (EloquentBuilder $query, string $search) {
                        return $query->where('unit_kerja.unit_name', 'like', "%{$search}%");
                    }),

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

                // $this->makeSearchableColumn('analysis', 'Analisis', 'imut_penilaians.analysis'),
                // $this->makeSearchableColumn('recommendations', 'Rekomendasi', 'imut_penilaians.recommendations'),
            ])
            ->filters([
                SelectFilter::make('imut_kategori')
                    ->label('Imut Kategori')
                    ->options(fn() => ImutCategory::pluck('short_name', 'id')->toArray())
                    ->attribute('imut_kategori_id')
                    ->multiple()
                    ->placeholder('Semua Kategori'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(SummaryUnitKerjaReportDetailExport::class)
                    ->label('Ekspor laporan IMUT Unit Kerja')
                    ->color('gray'),
            ])
            ->actions([])
            // ->recordAction('lihat')
            ->bulkActions([]);
    }

    protected function buildIsiPenilaianAction(): Action
    {
        $livewireComponent = $this;

        return Action::make('isi_penilaian')
            ->label('Isi Penilaian')
            ->icon('heroicon-o-pencil-square')
            ->color('primary')
            ->hiddenLabel()
            ->button()
            ->slideOver()
            ->modalHeading(fn($record) => 'Penilaian: ' . ($record->imut_data ?? ''))
            ->modalSubmitActionLabel('Simpan')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->mountUsing(function (Form $form, $record) {
                $form->fill([
                    'numerator_value'   => $record->numerator_value ?? null,
                    'denominator_value' => $record->denominator_value ?? null,
                    'analysis'          => $record->analysis ?? '',
                    'recommendations'   => $record->recommendations ?? '',
                ]);
            })
            ->form(function () use ($livewireComponent) {
                return [
                    Section::make('Perhitungan')
                        ->schema($this->buildPerhitunganSchemaForAction($livewireComponent))
                        ->columns(3),

                    Section::make('Unggah Bukti Pendukung')
                        ->schema($this->getMediaUploadFieldForAction($livewireComponent)),

                    Section::make('Analisis dan Rekomendasi')
                        ->schema($this->buildAnalysisSchemaForAction($livewireComponent)),
                ];
            })
            ->action(function ($record, array $data) {
                $penilaian = ImutPenilaian::find($record->id);

                if (!$penilaian) {
                    return;
                }

                $penilaian->update([
                    'numerator_value'   => $data['numerator_value'] ?? null,
                    'denominator_value' => $data['denominator_value'] ?? null,
                    'analysis'          => $data['analysis'] ?? null,
                    'recommendations'   => $data['recommendations'] ?? null,
                ]);
            })
            ->successNotificationTitle('Penilaian berhasil disimpan')
            ->after(fn() => $this->dispatch('$refresh'));
    }

    /**
     * Get upload directory dengan periode folder untuk laporan IMUT
     */
    protected function getUploadDirectory(string $collection): string
    {
        $baseDirectory = 'uploads/imut-documents/' . $collection;

        // Jika collection adalah laporan-imut, tambah periode folder
        if (str_ends_with($collection, '-laporan-imut')) {
            if ($this->laporan) {
                $periodFolder = $this->laporan->getPeriodeFolderName();
                return $baseDirectory . '/' . $periodFolder;
            }
        }

        // Untuk subfolder lain (dokumen-mutu, sop-panduan, etc.) langsung ke folder
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

    public function render()
    {
        return view('livewire.reports.imut-data-unit-kerja-detail-report');
    }
}
