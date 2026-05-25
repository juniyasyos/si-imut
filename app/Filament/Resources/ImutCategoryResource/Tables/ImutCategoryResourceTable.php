<?php

namespace App\Filament\Resources\ImutCategoryResource\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\ImutCategoryResource;
use App\Models\ImutCategory;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class ImutCategoryResourceTable
{
    public static function columns(): array
    {
        return [
            TextColumn::make('category_name')
                ->label(__('filament-forms::imut-category.fields.category_name'))
                ->searchable()
                ->sortable(),

            TextColumn::make('scope')
                ->badge()
                ->alignCenter()
                ->color(fn(string $state): string => match ($state) {
                    'global' => 'primary',
                    'internal' => 'success',
                    'national' => 'warning',
                    'unit' => 'gray',
                }),

            TextColumn::make('imut_data_count')
                ->label(__('filament-forms::imut-category.fields.data_count'))
                ->counts('imutData')
                ->badge()
                ->alignCenter()
                ->sortable(),

            IconColumn::make('is_use_global')
                ->label(__('filament-forms::imut-category.fields.is_use_global'))
                ->translateLabel()
                ->boolean()
                ->alignCenter()
                ->size('xl')
                ->disabled()
                ->tooltip(fn(Model $record) => $record->status ? 'Global' : 'Not Global')
                ->sortable(),

            IconColumn::make('is_benchmark_category')
                ->label(__('filament-forms::imut-category.fields.is_benchmark_category'))
                ->translateLabel()
                ->boolean()
                ->disabled()
                ->alignCenter()
                ->size('xl')
                ->tooltip(fn(Model $record) => $record->status ? 'Active' : 'Unactive')
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

    public static function actions(): array
    {
        return [
            EditAction::make()
                ->visible(fn($record) => method_exists($record, 'trashed') && ! $record->trashed()),

            DeleteAction::make()
                ->visible(fn($record) => method_exists($record, 'trashed') && ! $record->trashed()),

            ActionGroup::make([
                RestoreAction::make()
                    ->visible(
                        fn($record) =>
                        Gate::allows('restore', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),

                ForceDeleteAction::make()
                    ->visible(
                        fn($record) =>
                        Gate::allows('forceDelete', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),
            ]),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->label('Hapus (Soft Delete)'),
                ForceDeleteBulkAction::make()
                    ->label('Hapus Permanen')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Permanen Data Terpilih')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data ini secara permanen? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus Permanen'),
                RestoreBulkAction::make()
                    ->label('Pulihkan'),
            ]),
        ];
    }
}
