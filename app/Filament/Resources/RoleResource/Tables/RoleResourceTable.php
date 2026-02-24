<?php

namespace App\Filament\Resources\RoleResource\Tables;

use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Support\Str;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;

class RoleResourceTable
{
    public static function columns(): array
    {
        return [
            Tables\Columns\TextColumn::make('label')
                ->weight('font-medium')
                ->label(__('filament-shield::filament-shield.column.label'))
                ->formatStateUsing(fn($state): string => Str::headline($state))
                ->searchable(),
            Tables\Columns\TextColumn::make('guard_name')
                ->badge()
                ->color('warning')
                ->label(__('filament-shield::filament-shield.column.guard_name')),
            Tables\Columns\TextColumn::make('team.name')
                ->default('Global')
                ->badge()
                ->color(fn(mixed $state): string => str($state)->contains('Global') ? 'gray' : 'primary')
                ->label(__('filament-shield::filament-shield.column.team'))
                ->searchable()
                ->visible(fn(): bool => RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled()),
            Tables\Columns\TextColumn::make('permissions_count')
                ->badge()
                ->label(__('filament-shield::filament-shield.column.permissions'))
                ->counts('permissions')
                ->colors(['success']),
            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('filament-shield::filament-shield.column.updated_at'))
                ->dateTime(),
        ];
    }

    public static function filters(): array
    {
        return [
            //
        ];
    }

    public static function actions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            // Tables\Actions\DeleteBulkAction::make(),
        ];
    }
}
