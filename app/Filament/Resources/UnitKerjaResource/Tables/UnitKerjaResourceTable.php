<?php

namespace App\Filament\Resources\UnitKerjaResource\Tables;

use App\Filament\Exports\UnitKerjaExporter;
use App\Filament\Resources\UnitKerjaResource;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\ImutDataRelationManager;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\UsersRelationManager;
use App\Models\UnitKerja;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class UnitKerjaResourceTable
{
    public static function columns(): array
    {
        return [
            TextColumn::make('unit_name')
                ->label(__('filament-forms::unit-kerja.fields.unit_name'))
                ->description(fn(UnitKerja $record) => $record->description)
                ->wrap()
                ->grow()
                ->weight(FontWeight::Bold)
                ->searchable(),

            TextColumn::make('imut_data_count')
                ->label(__('filament-forms::imut-category.fields.data_count'))
                ->counts('imutData')
                ->badge()
                ->alignCenter()
                ->sortable(),
        ];
    }

    public static function filters(): array
    {
        return [
            TrashedFilter::make()
                ->default('with'),
        ];
    }

    public static function headerActions(): array
    {
        return [
            ExportAction::make()->exporter(UnitKerjaExporter::class),
        ];
    }

    public static function actions(): array
    {
        return [
            \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('users')
                ->slideOver()
                ->label('Pegawai')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->visible(UnitKerjaResource::isCrudAllowed())
                ->relationManager(UsersRelationManager::make()),

            \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('imutData')
                ->slideOver()
                ->label('Imut Data')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->relationManager(ImutDataRelationManager::make()),

            ActionGroup::make([
                EditAction::make('edit')
                    ->label('Edit')
                    ->tooltip('Edit')
                    ->visible(fn($record) => method_exists($record, 'trashed') && ! $record->trashed() && UnitKerjaResource::isCrudAllowed())
                    ->icon('heroicon-o-pencil-square'),

                RestoreAction::make('restore')
                    ->visible(
                        fn($record) =>
                        Gate::allows('restore', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed() &&
                            UnitKerjaResource::isCrudAllowed()
                    ),

                ForceDeleteAction::make('forceDelete')
                    ->requiresConfirmation()
                    ->visible(
                        fn($record) =>
                        Gate::allows('forceDelete', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed() &&
                            UnitKerjaResource::isCrudAllowed()
                    ),
            ]),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            // Keep bulk actions minimal: soft-delete and restore. Force delete remains but restricted.
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->visible(UnitKerjaResource::isCrudAllowed())
                    ->label('Hapus'),

                RestoreBulkAction::make()
                    ->visible(UnitKerjaResource::isCrudAllowed())
                    ->label('Pulihkan'),

                ForceDeleteBulkAction::make()
                    ->label('Hapus Permanen')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Permanen Data Terpilih')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data ini secara permanen? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus Permanen')
                    ->visible(fn() => Gate::allows('forceDelete', UnitKerja::class) && UnitKerjaResource::isCrudAllowed()),
            ])->visible(fn() => Gate::any(['update_imut::category', 'create_imut::category']) && UnitKerjaResource::isCrudAllowed()),
        ];
    }
}
