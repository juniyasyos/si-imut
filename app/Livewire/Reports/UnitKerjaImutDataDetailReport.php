<?php

namespace App\Livewire\Reports;

use App\Filament\Exports\SummaryUnitKerjaReportDetailExport;
use App\Filament\Resources\ImutPenilaianResource\Schema\ImutPenilaianResourceSchema;
use App\Models\ImutCategory;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use App\Services\Form\FormCalculationService;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Livewire\Component;

class UnitKerjaImutDataDetailReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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
            ->query(fn() => LaporanUnitKerja::getReportByUnitKerjaDetails($this->laporanId, $this->unitKerjaId))
            ->columns([
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
                    ->label(fn() => 'Export Laporan ' . UnitKerja::where('id', $this->unitKerjaId)->value('unit_name'))
                    ->color('gray'),
            ])
            ->actions([
                $this->buildIsiPenilaianAction(),
                $this->buildLihatDetailAction(),
            ])
            ->recordAction('isi_penilaian')
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
            ->modalHeading(fn($record) => ($record->imut_data ?? ''))
            ->modalSubmitActionLabel('Simpan')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            // ->disabled(fn() => $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian'))
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

    protected function buildLihatDetailAction(): Action
    {
        return Action::make('lihat')
            ->label('Lihat Detail')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->url(fn($record) => $this->getPenilaianUrl($record));
    }

    protected function getPenilaianUrl($record): string
    {
        $laporanSlug = LaporanImut::findOrFail($record->laporan_imut_id)->slug;

        return \App\Filament\Resources\LaporanImutResource::getUrl('edit-penilaian', [
            'laporanSlug' => $laporanSlug,
            'record' => $record->id,
        ]);
    }

    protected function getCategoryColor(int $categoryId): string
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
        return $colors[$categoryId % count($colors)];
    }

    protected function getPercentageColor($record): ?string
    {
        if (!is_numeric($record->percentage) || !is_numeric($record->imut_standard)) {
            return null;
        }

        // Check if meets standard (green)
        $meetsStandard = match ($record->imut_standard_type_operator) {
            '=' => $record->percentage == $record->imut_standard,
            '>=' => $record->percentage >= $record->imut_standard,
            '<=' => $record->percentage <= $record->imut_standard,
            '<' => $record->percentage < $record->imut_standard,
            '>' => $record->percentage > $record->imut_standard,
            default => false,
        };

        if ($meetsStandard) {
            return 'success';
        }

        // Check if within 80% threshold (yellow)
        $meetsThreshold = match ($record->imut_standard_type_operator) {
            '=' => $record->percentage == ($record->imut_standard * 0.8),
            '>=' => $record->percentage >= ($record->imut_standard * 0.8),
            '<=' => $record->percentage <= ($record->imut_standard * 1.2),
            '<' => $record->percentage < ($record->imut_standard * 1.2),
            '>' => $record->percentage > ($record->imut_standard * 0.8),
            default => false,
        };

        return $meetsThreshold ? 'warning' : 'danger';
    }

    protected function makeSearchableColumn(string $name, string $label, string $dbColumn): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->toggleable()
            ->limit(80)
            ->searchable(
                query: fn(EloquentBuilder $query, string $search) => $query->where($dbColumn, 'like', "%{$search}%")
            );
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
                ->readOnly($shouldLock)
                ->afterStateUpdated(fn(callable $set, callable $get) => $this->updateResultForAction($set, $get)),

            TextInput::make('denominator_value')
                ->label('Denominator')
                ->numeric()
                ->placeholder('0.00')
                ->nullable()
                ->debounce(1000)
                ->readOnly($shouldLock)
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

    protected function buildAnalysisSchemaForAction($livewireComponent): array
    {
        $shouldLock = $livewireComponent->isLaporanPeriodClosed() && Gate::denies('force_editable_imut::penilaian');
        $canRecommend = Gate::allows('create_recommendation_penilaian_imut::penilaian') || Gate::allows('force_editable_imut::penilaian');

        return [
            Textarea::make('analysis')
                ->label('Analisis')
                ->rows(4)
                ->nullable()
                ->readOnly($shouldLock)
                ->placeholder('Tuliskan hasil analisis (opsional)...')
                ->columnSpanFull(),

            Textarea::make('recommendations')
                ->label('Rekomendasi')
                ->nullable()
                ->disabled(!$canRecommend)
                ->rows(4)
                ->placeholder('Berikan saran atau rekomendasi (opsional)...')
                ->columnSpanFull(),
        ];
    }

    protected function updateResultForAction(callable $set, callable $get): void
    {
        $formCalculationService = app(FormCalculationService::class);
        $formCalculationService->updatePenilaianResult($set, $get);
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

    public function render()
    {
        return view('livewire.reports.unit-kerja-imut-data-detail-report');
    }
}
