<?php

namespace App\Livewire\Reports;

use App\Filament\Exports\SummaryUnitKerjaReportDetailExport;
use App\Filament\Resources\ImutPenilaianResource\Schema\ImutPenilaianResourceSchema;
use App\Models\ImutCategory;
use App\Models\ImutPenilaian;
use App\Models\LaporanUnitKerja;
use Filament\Forms;
use App\Models\UnitKerja;
use Filament\Forms\Components\Section;
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
use Illuminate\Support\Number;
use Livewire\Component;

class UnitKerjaImutDataDetailReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $laporanId = null;

    public ?int $unitKerjaId = null;

    public ?int $currentPenilaianId = null;

    protected $listeners = [
        'report-changed' => 'updateReport',
        'refreshTable' => '$refresh',
    ];

    public function updateReport(int $laporanId, int $unitKerjaId): void
    {

        $this->laporanId = $laporanId;
        $this->unitKerjaId = $unitKerjaId;
        $this->dispatch('$refresh');
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
                    ->searchable(query: fn(EloquentBuilder $query, string $search) => $query->where('imut_data.title', 'like', "%{$search}%")),

                TextColumn::make('imut_kategori')
                    ->label('Imut Kategori')
                    ->toggleable()
                    ->sortable()
                    ->color(function ($record) {
                        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
                        $id = $record->imut_kategori_id;
                        return $colors[$id % count($colors)];
                    })
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
                    ->color(fn($record) => match (true) {
                        ! is_numeric($record->percentage) || ! is_numeric($record->imut_standard) => null,

                        match ($record->imut_standard_type_operator) {
                            '=' => $record->percentage == $record->imut_standard,
                            '>=' => $record->percentage >= $record->imut_standard,
                            '<=' => $record->percentage <= $record->imut_standard,
                            '<' => $record->percentage < $record->imut_standard,
                            '>' => $record->percentage > $record->imut_standard,
                            default => false,
                        } => 'success',

                        match ($record->imut_standard_type_operator) {
                            '=' => $record->percentage == ($record->imut_standard * 0.8),
                            '>=' => $record->percentage >= ($record->imut_standard * 0.8),
                            '<=' => $record->percentage <= ($record->imut_standard * 1.2),
                            '<' => $record->percentage < ($record->imut_standard * 1.2),
                            '>' => $record->percentage > ($record->imut_standard * 0.8),
                            default => false,
                        } => 'warning',

                        default => 'danger',
                    })
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
                // ->summarize(Summarizer::make()
                //     ->label('Standar Min')
                //     ->suffix('%')
                //     ->using(fn(Builder $query) => $query->min('standard'))),

                // $this->makeSearchableColumn('analysis', 'Analisis', 'imut_penilaians.analysis'),
                // // $this->makeSearchableColumn('document_upload', 'Dokumen Upload', 'imut_penilaians.document_upload'),
                // $this->makeSearchableColumn('recommendations', 'Rekomendasi', 'imut_penilaians.recommendations'),
            ])
            ->filters([
                SelectFilter::make('imut_kategori')
                    ->label('Imut Kategori')
                    ->options(
                        fn() => ImutCategory::query()
                            ->pluck('short_name', 'id')
                            ->toArray()
                    )
                    ->attribute('imut_kategori_id')
                    ->multiple()
                    ->placeholder('Semua Kategori'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(SummaryUnitKerjaReportDetailExport::class)
                    ->label(fn() => 'Export Laporan ' . UnitKerja::where('id', $this->unitKerjaId)->value('unit_name'))
                    ->color('gray')
            ])
            ->actions([
                Action::make('lihat')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(function ($record) {
                        $laporanSlug = \App\Models\LaporanImut::findOrFail($record->laporan_imut_id)->slug;
                        return \App\Filament\Resources\LaporanImutResource::getUrl('edit-penilaian', [
                            'laporanSlug' => $laporanSlug,
                            'record' => $record->id,
                        ]);
                    }),
                Action::make('isi_penilaian')
                    ->label('Isi Penilaian')
                    ->hiddenLabel()
                    ->color('primary')
                    ->slideOver()
                    ->modalHeading(fn($record) => 'Penilaian: ' . ($record->imut_data ?? ''))
                    ->modalSubmitActionLabel('Simpan')
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalCloseButton(true)
                    ->modalCancelAction(false)
                    ->fillForm(fn($record): array => [
                        'numerator_value'   => $record->numerator_value ?? 0,
                        'denominator_value' => $record->denominator_value ?? 0,
                        'analysis'          => $record->analysis ?? '',
                        'recommendations'   => $record->recommendations ?? '',
                    ])
                    ->form([
                        Section::make('Perhitungan')
                            ->schema(ImutPenilaianResourceSchema::penilaianCalculationSchema())
                            ->columns(3),

                        Section::make('Analisis dan Rekomendasi')
                            ->schema(ImutPenilaianResourceSchema::penilaianAnalysisSchema()),
                    ])
                    ->action(function ($record, array $data) {
                        $penilaian = ImutPenilaian::find($record->id);

                        if (!$penilaian) {
                            return;
                        }

                        $penilaian->update([
                            'numerator_value'   => $data['numerator_value'] ?? 0,
                            'denominator_value' => $data['denominator_value'] ?? 0,
                            'analysis'          => $data['analysis'] ?? null,
                            'recommendations'   => $data['recommendations'] ?? null,
                        ]);
                    })
                    ->successNotificationTitle('Penilaian berhasil disimpan')
                    ->after(function () {
                        $this->dispatch('$refresh');
                    })
            ])
            ->recordAction('isi_penilaian')
            ->bulkActions([]);
    }

    protected function makeSearchableColumn(string $name, string $label, string $dbColumn): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->toggleable()
            ->searchable(
                query: fn(EloquentBuilder $query, string $search) => $query->where($dbColumn, 'like', "%{$search}%")
            );
    }

    public function render()
    {
        return view('livewire.reports.unit-kerja-imut-data-detail-report');
    }
}
