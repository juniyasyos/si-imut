<?php

namespace App\Livewire\Overview;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Illuminate\Database\Eloquent\Model;
use App\Models\RegionType;
use Filament\Actions\Action;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Models\ImutCategory;
use App\Services\Reporting\ImutReportService;
use App\Traits\HasPercentageColor;
use App\Traits\HasTableHelpers;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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

class ImutDataSummaryTable extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPercentageColor;
    use HasTableHelpers;

    public ?int $imutDataId = null;

    protected $listeners = [
        'summary-changed' => 'updateSummary',
    ];

    public function updateSummary(int $imutDataId): void
    {
        $this->imutDataId = $imutDataId;
        $this->dispatch('$refresh');
    }

    public function getTableRecordKey(Model|array $record): string
    {
        if (! $record) {
            return (string) uniqid('record_', true);
        }

        // Since we're grouping by laporan_imut_id, use that as the key
        return (string) ($record->laporan_imut_id ?? uniqid('record_', true));
    }

    public function table(Table $table): Table
    {
        $reportService = app(ImutReportService::class);
        
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
                ->color(fn($record) => $this->getPercentageColor($record))
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
        $regionTypes = RegionType::all();
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
            ->query(fn() => $reportService->getSummaryForImutDataTable($this->imutDataId))
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
            ->recordActions([
                Action::make('view_details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(function ($record) {
                        return ImutDataReport::getUrl([
                            'laporan_id' => $record->laporan_imut_id,
                        ]);
                    }),
            ])
            ->recordUrl(function ($record) {
                return ImutDataReport::getUrl([
                    'laporan_id' => $record->laporan_imut_id,
                ]);
            })
            ->toolbarActions([]);
    }

    public function render()
    {
        return view('livewire.overview.imut-data-summary-table');
    }
}
