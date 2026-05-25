<?php

namespace App\Filament\Resources\RoleResource\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Support\Str;
use Filament\Tables;

class RoleResourceTable
{
    public static function columns(): array
    {
        return [
            TextColumn::make('label')
                ->weight('font-medium')
                ->label(__('filament-shield::filament-shield.column.label'))
                ->formatStateUsing(fn($state): string => Str::headline($state))
                ->searchable(),
            TextColumn::make('guard_name')
                ->badge()
                ->color('warning')
                ->label(__('filament-shield::filament-shield.column.guard_name')),
            TextColumn::make('team.name')
                ->default('Global')
                ->badge()
                ->color(fn(mixed $state): string => str($state)->contains('Global') ? 'gray' : 'primary')
                ->label(__('filament-shield::filament-shield.column.team'))
                ->searchable()
                ->visible(fn(): bool => RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled()),
            TextColumn::make('permissions_count')
                ->badge()
                ->label(__('filament-shield::filament-shield.column.permissions'))
                ->counts('permissions')
                ->colors(['success']),
            TextColumn::make('updated_at')
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
            DeleteAction::make()
                ->visible(!(env('USE_SSO') && env('IAM_ENABLED')) && config('iam.role_sync_mode') !== 'pull'),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            // Tables\Actions\DeleteBulkAction::make(),
        ];
    }
}
