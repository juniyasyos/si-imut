<?php

namespace App\Filament\Resources\ImutProfileResource\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use App\Filament\Resources\ImutProfileResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Model;

class ImutProfileResourceTable
{
    public static function columns(): array
    {
        return [
            TextColumn::make('version')
                ->label('Versi')
                ->searchable()
                ->sortable(),

            TextColumn::make('indicator_type')
                ->label('Tipe Indikator')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'process' => 'info',
                    'output' => 'warning',
                    'outcome' => 'success',
                    default => 'gray',
                }),

            TextColumn::make('target')
                ->label('Target')
                ->formatStateUsing(fn($state, $record) => trim(($record->target_operator ?? '') . ' ' . ($record->target_value !== null ? "{$record->target_value}%" : '')))
                ->searchable()
                ->sortable(),

            TextColumn::make('responsible_person')
                ->label('Penanggung Jawab')
                ->searchable()
                ->limit(20),

            TextColumn::make('form_template_status')
                ->label('Form Template')
                ->getStateUsing(fn($record) => $record->formTemplates()->exists() ? 'Ada' : 'Belum Ada')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'Ada' => 'success',
                    'Belum Ada' => 'warning',
                    default => 'gray',
                }),
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
            EditAction::make(),
            DeleteAction::make(),
            RestoreAction::make()
                ->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
            ForceDeleteAction::make()
                ->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make(),
            ]),
        ];
    }
}
