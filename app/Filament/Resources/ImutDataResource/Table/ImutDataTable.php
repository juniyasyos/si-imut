<?php

namespace App\Filament\Resources\ImutDataResource\Table;

use App\Filament\Exports\ImutDataExporter;
use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\Pages\ImutDataUnitKerjaOverview;
use App\Filament\Resources\ImutDataResource\Pages\SummaryImutDataDiagram;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use App\Models\ImutData;
use App\Models\User;
use App\Services\Filament\ImutDataFilamentService;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImutDataTable extends ImutDataResource
{
    public static function query(): Builder
    {
        return app(ImutDataFilamentService::class)->getTableQuery();
    }

    public static function columns(): array
    {
        return [
            TextColumn::make('title')
                ->label(__('filament-forms::imut-data.fields.title'))
                ->tooltip(fn(ImutData $record): string => $record->description ?? '-')
                ->searchable()
                ->sortable()
                ->limit(60),

            TextColumn::make('categories.short_name')
                ->label(__('filament-forms::imut-data.fields.imut_kategori_id'))
                ->badge()
                ->sortable()
                ->color(function ($record) {
                    $categoryId = $record->categories->id ?? 0;
                    return app(ImutDataFilamentService::class)->getCategoryBadgeColor($categoryId);
                })
                ->toggleable(isToggledHiddenByDefault: false),

            \Archilex\ToggleIconColumn\Columns\ToggleIconColumn::make('status')
                ->label(__('filament-forms::imut-data.fields.status'))
                ->translateLabel()
                ->alignCenter()
                ->size('xl')
                ->disabled(fn() => !app(ImutDataFilamentService::class)->canToggleStatus())
                ->tooltip(fn(Model $record) => $record->status ? 'Active' : 'Unactive')
                ->sortable(),

            TextColumn::make('created_at')
                ->label(__('filament-forms::imut-data.fields.created_at'))
                ->dateTime('d M Y H:i')
                ->sortable()
                ->icon('heroicon-o-calendar')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function actions(): array
    {
        return [
            // \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('user-relation-manager')
            //     // ->slideOver()
            //     ->label('Imut Profile')
            //     ->color('success')
            //     ->icon('heroicon-c-document-plus')
            //     ->relationManager(ProfilesRelationManager::make())
            //     ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_any_imut::profile', User::class)),

            ActionTable::make('lihat_berdasarkan_unit_kerja')
                ->label('🏢 Lihat Grafik')
                ->color('success')
                ->visible(function () {
                    $user = Auth::user();

                    return $user->can('view_by_unit_kerja_imut::data') &&
                        ! $user->can('view_all_data_imut::data') &&
                        $user->unitKerjas->isNotEmpty();
                })
                ->url(function ($record) {
                    $user = Auth::user();
                    $unitKerja = $user->unitKerjas->first();

                    if (! $unitKerja) {
                        return '#';
                    }

                    return ImutDataUnitKerjaOverview::getUrl([
                        'record_imut_data' => $record->id,
                        'record_unit_kerja' => $unitKerja->id,
                    ]);
                }),

            EditAction::make()
                ->label(fn($record) => (
                    $record && $record->created_by !== Auth::id() && !Auth::user()->can('force_editable_imut::profile')
                ) ? 'Lihat' : 'Ubah')
                ->icon(fn($record) => (
                    $record && $record->created_by !== Auth::id() && !Auth::user()->can('force_editable_imut::profile')
                ) ? 'heroicon-o-eye' : 'heroicon-o-pencil-square')
                ->visible(fn($record) => !is_null($record)),

            ActionGroup::make([
                ActionTable::make('lihat_berdasarkan_imut_data')
                    ->label('📊 IMUT DATA')
                    ->color('primary')
                    ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                    ->url(fn($record) => SummaryImutDataDiagram::getUrl(['record' => $record->slug])),

                \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('unit-kerja-relation')
                    ->slideOver()
                    ->label('🏢 Unit Kerja')
                    ->color('primary')
                    ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                    ->relationManager(\App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager::make()),
            ])
                ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                ->icon('heroicon-s-chart-bar')
                ->label('Lihat Grafik')
                ->button(),

            ActionGroup::make([
                RestoreAction::make()
                    ->visible(
                        fn($record) => \Illuminate\Support\Facades\Gate::allows('restore', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),

                ForceDeleteAction::make()
                    ->visible(
                        fn($record) => \Illuminate\Support\Facades\Gate::allows('forceDelete', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),
            ]),
        ];
    }

    public static function filters(): array
    {
        return [
            TrashedFilter::make()
                ->default('with'),
            SelectFilter::make('imut_kategori_id')
                ->label('Kategori IMUT')
                ->preload()
                ->multiple()
                ->relationship('categories', 'short_name')
                ->searchable(),
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
                DeleteBulkAction::make(),
                RestoreBulkAction::make()
                    ->visible(fn() => method_exists(static::class, 'bootSoftDeletes')),
                ForceDeleteBulkAction::make()
                    ->visible(fn() => method_exists(static::class, 'bootSoftDeletes')),
            ]),
        ];
    }
}
