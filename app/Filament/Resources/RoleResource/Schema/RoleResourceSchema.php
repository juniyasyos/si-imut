<?php

namespace App\Filament\Resources\RoleResource\Schema;

use App\Filament\Resources\RoleResource;
use Filament\Facades\Filament;
use Filament\Forms;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\HtmlString;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Forms\ShieldSelectAllToggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class RoleResourceSchema
{
    public static function make(): array
    {
        return [
            Grid::make()
                ->schema([
                    Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('label')
                                ->label(__('filament-shield::filament-shield.field.label'))
                                ->unique(ignoreRecord: true)
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('name')
                                ->label(__('filament-shield::filament-shield.field.name'))
                                ->unique(ignoreRecord: true)
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('guard_name')
                                ->label(__('filament-shield::filament-shield.field.guard_name'))
                                ->default(Utils::getFilamentAuthGuard())
                                ->nullable()
                                ->maxLength(255),

                            Forms\Components\Select::make(config('permission.column_names.team_foreign_key'))
                                ->label(__('filament-shield::filament-shield.field.team'))
                                ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                /** @phpstan-ignore-next-line */
                                ->default([Filament::getTenant()?->id])
                                ->options(fn(): Arrayable => Utils::getTenantModel() ? Utils::getTenantModel()::pluck('name', 'id') : collect())
                                ->hidden(fn(): bool => !(RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled()))
                                ->dehydrated(fn(): bool => !(RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled())),

                            ShieldSelectAllToggle::make('select_all')
                                ->onIcon('heroicon-s-shield-check')
                                ->offIcon('heroicon-s-shield-exclamation')
                                ->label(__('filament-shield::filament-shield.field.select_all.name'))
                                ->helperText(fn(): HtmlString => new HtmlString(__('filament-shield::filament-shield.field.select_all.message')))
                                ->dehydrated(fn(bool $state): bool => $state),
                        ])
                        ->columns([
                            'sm' => 2,
                            'lg' => 3,
                        ]),
                ]),
            RoleResource::getShieldFormComponents(),
        ];
    }
}
