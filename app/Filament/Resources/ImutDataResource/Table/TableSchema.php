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

            TextColumn::make('categories.short_name')
                ->label(__('filament-forms::imut-data.fields.imut_kategori_id'))
                ->badge()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false)
                ->color(function ($record) {
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

            \Archilex\ToggleIconColumn\Columns\ToggleIconColumn::make('is_monthly')
                ->label('Pengisian')
                ->translateLabel()
                ->alignCenter()
                ->size('xl')
                ->tooltip(fn(Model $record) => $record->is_monthly ? 'Harian' : 'Bulanan')
                ->sortable()
                ->disabled(),
        ];
    }

    public static function actions(): array
    {
        return [
            EditAction::make('edit')
                ->label('')
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
                    ->label('Unit Kerja')
                    ->icon('heroicon-o-building-office-2')
                    ->color('info')
                    ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                    ->relationManager(\App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager::make()),

                \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('profiles')
                    ->slideOver()
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
                ->tooltip('Lainnya'),
        ];
    }

    public static function filters(): array
    {
        return [
            TrashedFilter::make('status')
                ->default('with'),
            SelectFilter::make('imut_kategori_id')
                ->label('Kategori IMUT')
                ->preload()
                ->multiple()
                ->relationship('categories', 'short_name')
                ->searchable(),
            SelectFilter::make('is_monthly')
                ->label('Pengisian Bulanan')
                ->options([1 => 'Ya', 0 => 'Tidak'])
                ->preload(),
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
