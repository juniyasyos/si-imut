<?php

namespace App\Livewire\Overview;

use App\Models\ImutCategory;
use App\Models\LaporanUnitKerja;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
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

class ImutDataSummaryTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $imutDataId = null;

    protected $listeners = [
        'summary-changed' => 'updateSummary',
    ];

    public function updateSummary(int $imutDataId): void
    {
        $this->imutDataId = $imutDataId;
        $this->dispatch('$refresh');
    }

    public function getTableRecordKey($record): string
    {
        if (! $record) {
            return (string) uniqid('record_', true);
        }

        // Since we're grouping by laporan_imut_id, use that as the key
        return (string) ($record->laporan_imut_id ?? uniqid('record_', true));
    }

    public function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('laporan_name')
                ->label('Nama Laporan')
                ->grow()
                ->wrap()
                ->sortable()
                ->searchable(query: fn(EloquentBuilder $query, string $search) => $query->where('laporan_imuts.name', 'like', "%{$search}%")),

            TextColumn::make('periode_pengisian')
                ->label('Periode Pengisian')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('laporan_status')
                ->label('Status Laporan')
                ->badge()
                ->alignCenter()
                ->color(fn(string $state): string => match ($state) {
                    'coming_soon' => 'gray',
                    'process' => 'primary',
                    'complete' => 'success',
                })
                ->sortable()->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('total_numerator')
                ->label('Total N')
                ->alignCenter()
                ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                ->summarize(
                    Summarizer::make()
                        ->label('Grand Total N')
                        ->using(fn(Builder $query) => number_format($query->sum('total_numerator'), 2))
                ),

            TextColumn::make('total_denominator')
                ->label('Total D')
                ->alignCenter()
                ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                ->summarize(
                    Summarizer::make()
                        ->label('Grand Total D')
                        ->using(fn(Builder $query) => number_format($query->sum('total_denominator'), 2))
                ),

            TextColumn::make('percentage')
                ->label('Persentase (%)')
                ->alignCenter()
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
                            $n = $query->sum('total_numerator');
                            $d = $query->sum('total_denominator');

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

        // Add dynamic benchmarking columns
        $regionTypes = \App\Models\RegionType::all();
        foreach ($regionTypes as $regionType) {
            $columns[] = TextColumn::make("benchmark_{$regionType->id}")
                ->label("{$regionType->type}")
                ->suffix('%')
                ->toggleable()
                ->alignCenter()
                ->formatStateUsing(function ($state) {
                    if (empty($state) || $state == 0) {
                        return '-';
                    }
                    return Number::format($state, 2, locale: app()->getLocale());
                })
                ->color('warning')
                ->badge();
        }

        $columns[] = TextColumn::make('unit_count')
            ->label('Jumlah Unit Kerja')
            ->alignCenter()
            ->toggleable(isToggledHiddenByDefault: true)
            ->summarize(
                Summarizer::make()
                    ->label('Total Unit')
                    ->using(fn(Builder $query) => $query->sum('unit_count'))
            );

        return $table
            ->query(fn() => LaporanUnitKerja::getSummaryByImutDataGrouped($this->imutDataId))
            ->columns($columns)
            ->filters([
                SelectFilter::make('laporan_status')
                    ->label('Status Laporan')
                    ->options([
                        'coming_soon' => 'Akan Datang',
                        'process' => 'Proses',
                        'complete' => 'Selesai',
                    ])
                    ->multiple()
                    ->placeholder('Semua Status'),

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
            ->actions([
                Action::make('view_details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(function ($record) {
                        return \App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport::getUrl([
                            'laporan_id' => $record->laporan_imut_id,
                        ]);
                    }),
            ])
            ->recordUrl(function ($record) {
                return \App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport::getUrl([
                    'laporan_id' => $record->laporan_imut_id,
                ]);
            })
            ->bulkActions([]);
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

    public function render()
    {
        return view('livewire.overview.imut-data-summary-table');
    }
}
