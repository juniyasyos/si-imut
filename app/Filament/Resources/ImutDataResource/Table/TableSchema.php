<?php

namespace App\Filament\Resources\ImutDataResource\Table;

use Illuminate\Support\Facades\Auth;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Gate;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Carbon\Carbon;
use Filament\Actions\ExportAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Exports\ImutDataExporter;
use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\Pages\SummaryDiagram;
use App\Models\User;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TableSchema
{
    public static function query(): Builder
    {
        $user = Auth::user();

        return app(ImutDataRepositoryInterface::class)->getTableQueryForUser($user);
    }

    public static function columns(): array
    {
        return [
            TextColumn::make('title')
                ->label(__('filament-forms::imut-data.fields.title'))
                ->searchable()
                ->sortable()
                ->wrap()
                ->weight('medium'),

            TextColumn::make('categories.category_name')
                ->label(__('filament-forms::imut-data.fields.imut_kategori_id'))
                ->badge()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false)
                ->color(function ($record) {
                    // dd([
                    //     'record' => $record,
                    //     'categories' => $record->categories,
                    //     'id' => $record->categories->id ?? null,
                    // ]);
                    $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
                    $id = $record->categories->id ?? 0;

                    return $colors[$id % count($colors)];
                }),

            TextColumn::make('status')
                ->label(__('filament-forms::imut-data.fields.status'))
                ->badge()
                ->alignCenter()
                ->color(fn(Model $record) => $record->status ? 'success' : 'gray')
                ->formatStateUsing(fn(Model $record) => $record->status ? 'Aktif' : 'Nonaktif')
                ->toggleable(isToggledHiddenByDefault: false)
                ->sortable(),

            TextColumn::make('is_monthly')
                ->label('Tipe Pengisian')
                ->badge()
                ->icon('heroicon-o-calendar')
                ->formatStateUsing(
                    fn(bool $state) => $state ? 'Harian' : 'Bulanan'
                )
                ->color(
                    fn(bool $state) => $state ? 'info' : 'warning'
                )
                ->tooltip(
                    fn(bool $state) => $state
                    ? 'Pengisian dapat dilakukan rutin setiap 1x24 jam.'
                    : 'Pengisian hanya dilakukan 1 kali di akhir periode bulanan.'
                )
                ->alignCenter()
                ->sortable(),
        ];
    }

    public static function actions(): array
    {
        return [
            EditAction::make('edit')
                ->label('edit')
                ->tooltip('Edit')
                ->icon('heroicon-o-pencil-square')
                ->visible(fn($record) => !is_null($record)),

            ActionGroup::make([
                Action::make('summary')
                    ->label('Summary')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('success')
                    ->visible(fn() => Gate::allows('view_all_data_imut::data', User::class))
                    ->url(fn($record) => SummaryDiagram::getUrl(['record' => $record->slug])),

                Action::make('catatan')
                    ->label('Analisis & Rekomendasi per Triwulan/Tahun')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->slideOver()
                    ->modalWidth('8xl')
                    ->visible(fn() => Gate::allows('view_all_data_imut::data', User::class))
                    ->modalHeading(fn($record) => ($record->title ?? ''))
                    ->modalContent(fn($record) => view('filament.resources.imut-data-resource.widgets.imut-data-notes-slide-over', ['record' => $record])),

                RelationManagerAction::make('unit-kerja')
                    ->slideOver()
                    ->modalWidth('8xl')
                    ->label('Unit Kerja')
                    ->icon('heroicon-o-building-office-2')
                    ->color('info')
                    ->visible(fn() => Gate::allows('view_all_data_imut::data', User::class))
                    ->relationManager(UnitKerjaRelationManager::make()),

                RelationManagerAction::make('profiles')
                    ->slideOver()
                    ->modalWidth('8xl')
                    ->label('Profiles')
                    ->icon('heroicon-o-document-text')
                    ->color('')
                    ->relationManager(ProfilesRelationManager::make()),

                RestoreAction::make('restore')
                    ->visible(
                        fn($record) => Gate::allows('restore', $record) &&
                        method_exists($record, 'trashed') &&
                        $record->trashed()
                    ),

                ForceDeleteAction::make('forceDelete')
                    ->visible(
                        fn($record) => Gate::allows('forceDelete', $record) &&
                        method_exists($record, 'trashed') &&
                        $record->trashed()
                    ),
            ])
                ->icon('heroicon-o-ellipsis-vertical')
                ->button()
                ->label('aksi')
                ->tooltip('Lainnya'),
        ];
    }

    public static function filters(): array
    {
        return [
            // Status & Basic Filters
            TrashedFilter::make('status')
                ->label('Status Data')
                ->columnSpanFull()
                ->default('with'),

            SelectFilter::make('imut_kategori_id')
                ->label('Kategori IMUT')
                ->relationship('categories', 'short_name')
                ->multiple()
                ->preload()
                ->searchable()
                ->placeholder('Pilih kategori IMUT'),

            SelectFilter::make('is_monthly')
                ->label('Tipe Pengisian')
                ->options([
                    1 => 'Harian',
                    0 => 'Bulanan'
                ])
                ->placeholder('Pilih tipe pengisian')
                ->preload(),

            // Period Filters
            SelectFilter::make('active_year')
                ->label('Aktif pada Tahun')
                ->placeholder('Pilih tahun')
                ->options(function () {
                    $currentYear = now()->year;
                    $years = [];
                    for ($year = $currentYear - 3; $year <= $currentYear + 2; $year++) {
                        $years[$year] = $year;
                    }
                    return $years;
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (!empty($data['value'])) {
                        $year = $data['value'];
                        $startOfYear = "$year-01-01";
                        $endOfYear = "$year-12-31";

                        return $query->whereHas('profiles', function (Builder $q) use ($startOfYear, $endOfYear) {
                            $q->where('valid_from', '<=', $endOfYear)
                                ->where(function (Builder $q2) use ($startOfYear) {
                                    $q2->where('valid_until', '>=', $startOfYear)
                                        ->orWhereNull('valid_until');
                                });
                        });
                    }
                    return $query;
                }),

            Filter::make('active_period')
                ->label('Periode Aktif Kustom')
                ->schema([
                    DatePicker::make('from')
                        ->label('Mulai Tanggal')
                        ->placeholder('Pilih tanggal mulai')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->format('Y-m-d')
                        ->closeOnDateSelection(),
                    DatePicker::make('until')
                        ->label('Sampai Tanggal')
                        ->placeholder('Pilih tanggal akhir')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->format('Y-m-d')
                        ->closeOnDateSelection()
                        ->after('from'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when(
                        $data['from'] || $data['until'],
                        function (Builder $query) use ($data) {
                            $query->whereHas('profiles', function (Builder $q) use ($data) {
                                // Jika ada tanggal mulai dan akhir
                                if ($data['from'] && $data['until']) {
                                    $q->where('valid_from', '<=', $data['until'])
                                        ->where(function (Builder $q2) use ($data) {
                                            $q2->where('valid_until', '>=', $data['from'])
                                                ->orWhereNull('valid_until');
                                        });
                                }
                                // Jika hanya tanggal mulai
                                elseif ($data['from']) {
                                    $q->where(function (Builder $q2) use ($data) {
                                        $q2->where('valid_until', '>=', $data['from'])
                                            ->orWhereNull('valid_until');
                                    });
                                }
                                // Jika hanya tanggal akhir
                                elseif ($data['until']) {
                                    $q->where('valid_from', '<=', $data['until']);
                                }
                            });
                        }
                    );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if ($data['from'] && $data['until']) {
                        $indicators[] = 'Periode: ' .
                            Carbon::parse($data['from'])->format('d/m/Y') .
                            ' - ' .
                            Carbon::parse($data['until'])->format('d/m/Y');
                    } elseif ($data['from']) {
                        $indicators[] = 'Aktif dari: ' . Carbon::parse($data['from'])->format('d/m/Y');
                    } elseif ($data['until']) {
                        $indicators[] = 'Aktif sampai: ' . Carbon::parse($data['until'])->format('d/m/Y');
                    }

                    return $indicators;
                })
                ->columnSpanFull(),
        ];
    }

    public static function headerActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(ImutDataExporter::class),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make('delete')
                    ->label('Hapus (Soft Delete)'),
                ForceDeleteBulkAction::make('forceDelete')
                    ->label('Hapus Permanen')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Permanen Data Terpilih')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data ini secara permanen? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus Permanen'),
                RestoreBulkAction::make('restore')
                    ->label('Pulihkan'),
            ]),
        ];
    }
}
