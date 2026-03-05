<?php

namespace App\Filament\Resources\ImutDataResource\Table;

use App\Filament\Exports\ImutDataExporter;
use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\Pages\UnitKerjaOverview;
use App\Filament\Resources\ImutDataResource\Pages\SummaryDiagram;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use App\Models\ImutData;
use App\Models\User;
use Filament\Tables\Actions\Action as ActionTable;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TableSchema extends ImutDataResource
{
    public static function query(): Builder
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->can('view_all_data_imut::data')) {
            return ImutData::query();
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            $unitKerjaIds = $user->unitKerjas->pluck('id')->toArray();

            return ImutData::query()
                ->whereHas('unitKerja', function ($query) use ($unitKerjaIds) {
                    $query->whereIn('unit_kerja.id', $unitKerjaIds);
                })->orWhere('created_by', $user->id);
        }

        return ImutData::query()->whereRaw('1 = 0');
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
                ActionTable::make('summary')
                    ->label('Summary')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->color('success')
                    ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                    ->url(fn($record) => SummaryDiagram::getUrl(['record' => $record->slug])),

                \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('unit-kerja')
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->label('Unit Kerja')
                    ->icon('heroicon-o-building-office-2')
                    ->color('info')
                    ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                    ->relationManager(\App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager::make()),

                \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('profiles')
                    ->slideOver()
                    ->modalWidth('7xl')
                    ->label('Profiles')
                    ->icon('heroicon-o-document-text')
                    ->color('')
                    ->relationManager(\App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager::make()),

                RestoreAction::make('restore')
                    ->visible(
                        fn($record) => \Illuminate\Support\Facades\Gate::allows('restore', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),

                ForceDeleteAction::make('forceDelete')
                    ->visible(
                        fn($record) => \Illuminate\Support\Facades\Gate::allows('forceDelete', $record) &&
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
                ->form([
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
                            \Carbon\Carbon::parse($data['from'])->format('d/m/Y') .
                            ' - ' .
                            \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                    } elseif ($data['from']) {
                        $indicators[] = 'Aktif dari: ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                    } elseif ($data['until']) {
                        $indicators[] = 'Aktif sampai: ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
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
