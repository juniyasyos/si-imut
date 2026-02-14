<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\Schema\RoleResourceSchema;
use App\Filament\Resources\RoleResource\Tables\RoleResourceTable;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class RoleResource extends Resource implements HasShieldPermissions
{
    use HasActiveIcon, HasShieldFormComponents;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(RoleResourceSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(RoleResourceTable::columns())
            ->filters(RoleResourceTable::filters())
            ->actions(RoleResourceTable::actions())
            ->bulkActions(RoleResourceTable::bulkActions());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getCluster(): ?string
    {
        return Utils::getResourceCluster() ?? static::$cluster;
    }

    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }

    public static function getModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.roles');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // return Utils::isResourceNavigationRegistered();
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return Utils::isResourceNavigationGroupEnabled()
            ? __('filament-shield::filament-shield.nav.group')
            : '';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-shield::filament-shield.nav.role.label');
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shield-check';
    }

    public static function getNavigationSort(): ?int
    {
        return Utils::getResourceNavigationSort();
    }

    public static function getSlug(): string
    {
        return Utils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        return Utils::isResourceNavigationBadgeEnabled()
            ? strval(static::getEloquentQuery()->count())
            : null;
    }

    public static function isScopedToTenant(): bool
    {
        return Utils::isScopedToTenant();
    }

    public static function canGloballySearch(): bool
    {
        return Utils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
    }
}
