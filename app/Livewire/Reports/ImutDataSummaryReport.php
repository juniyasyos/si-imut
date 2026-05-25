<?php

namespace App\Livewire\Reports;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\ExportAction;
use Filament\Actions\Action;
use App\Filament\Exports\SummaryImutDataReportExport;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataUnitKerjaReport;
use App\Models\ImutCategory;
use App\Services\Reporting\ImutReportService;
use App\Traits\HasPercentageColor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Number;
use Livewire\Component;

class ImutDataSummaryReport extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPercentageColor;

    public ?int $laporanId = null;

    protected $listeners = [
        'report-changed' => 'updateReport',
    ];

    public function updateReport(int $laporanId): void
    {
        $this->laporanId = $laporanId;
        $this->dispatch('$refresh');
    }

    public function getTableRecordKey(Model|array $record): string
    {
        if (! $record || ! $record->getKey()) {
            return (string) uniqid('record_', true);
        }

        return (string) $record->getKey();
    }

    public function table(Table $table): Table
    {
        $reportService = app(ImutReportService::class);
        
        return $table
            ->query(fn() => $reportService->getImutDataSummaryData($this->laporanId))
            ->columns([
                TextColumn::make('imut_data_title')
                    ->label('IMUT Data')
                    ->wrap()
                    ->lineClamp(2)
                    ->width('30%')
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) {
                        return $query->where('imut_data.title', 'like', "%{$search}%");
                    }),

                TextColumn::make('completion_summary')
                    ->label('Capaian Pelaporan')
                    ->alignCenter()
                    ->toggleable()
                    ->state(
                        fn($record) =>
                        number_format($record->filled_count ?? 0) . ' dari ' . number_format($record->total_count ?? 0) . ' unit mengisi'
                    )
                    ->tooltip(
                        fn($record) =>
                        'Persentase: ' . Number::format($record->percentage_units ?? 0, 2, locale: app()->getLocale()) . '%'
                    )
                    ->color(fn($record) => match (true) {
                        !is_numeric($record->percentage_units) => null,
                        $record->percentage_units >= ($record->avg_standard ?? 100) => 'success',
                        $record->percentage_units >= (($record->avg_standard ?? 100) * 0.8) => 'warning',
                        default => 'danger',
                    })
                    ->sortable(
                        query: function($query, $direction) {
                            $filledCountExpr = "SUM(CASE WHEN imut_penilaians.numerator_value IS NOT NULL AND imut_penilaians.denominator_value IS NOT NULL AND imut_penilaians.denominator_value != 0 THEN 1 ELSE 0 END)";
                            return $query->orderByRaw("({$filledCountExpr} / NULLIF(COUNT(imut_penilaians.id), 0)) " . $direction);
                        }
                    )
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Capaian')
                            ->using(function (Builder $query) {
                                $n = $query->sum('filled_count');
                                $d = $query->sum('total_count');
                                return $d > 0
                                    ? Number::format(($n / $d) * 100, 2, locale: app()->getLocale()) . '%'
                                    : '0%';
                            })
                    ),

                TextColumn::make('imut_kategori')
                    ->label('Imut Kategori')
                    ->toggleable()
                    ->sortable()
                    ->color(fn($record) => $this->getCategoryColor($record->imut_kategori_id))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge(),

                TextColumn::make('total_numerator')
                    ->label('Numerator (N)')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Total N')
                            ->using(fn(Builder $query) => number_format($query->sum('total_numerator'), 2))
                    ),

                TextColumn::make('total_denominator')
                    ->label('Denumerator (D)')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Total D')
                            ->using(fn(Builder $query) => number_format($query->sum('total_denominator'), 2))
                    ),

                TextColumn::make('percentage')
                    ->label('Persentase (%)')
                    ->alignCenter()
                    ->suffix('%')
                    ->toggleable(isToggledHiddenByDefault: false)
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
            ])
            ->headerActions([
                ExportAction::make()->exporter(SummaryImutDataReportExport::class)->label('Ekspor laporan IMUT')
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
            ->recordActions([
                Action::make('details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => ImutDataUnitKerjaReport::getUrl([
                        'laporan_id' => $record->laporan_imut_id,
                        'imut_data_id' => $record->id,
                    ])),
                //
            ])
            ->recordUrl(fn($record) => ImutDataUnitKerjaReport::getUrl([
                'laporan_id' => $record->laporan_imut_id,
                'imut_data_id' => $record->id,
            ]))
            ->toolbarActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.reports.imut-data-summary-report');
    }
}
