<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Pages\ViewRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use Filament\Panel;
use App\Traits\HasActiveIcon;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Filament\Resources\RoleResource\Schema\RoleResourceSchema;
use App\Filament\Resources\RoleResource\Tables\RoleResourceTable;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class RoleResource extends Resource implements HasShieldPermissions
{
    private static ?string $navigationBadgeMemo = null;

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components(RoleResourceSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(RoleResourceTable::columns())
            ->filters(RoleResourceTable::filters())
            ->recordActions(RoleResourceTable::actions())
            ->toolbarActions(RoleResourceTable::bulkActions());
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
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
        return (bool) config('filament-shield.shield_resource.should_register_navigation', true);
    }

    public static function getNavigationGroup(): ?string
    {
        return (bool) config('filament-shield.shield_resource.navigation_group', false)
            ? __('filament-shield::filament-shield.nav.group')
            : null;
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
        return config('filament-shield.shield_resource.navigation_sort');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return Utils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        if (! (bool) config('filament-shield.shield_resource.navigation_count_badge', false)) {
            return null;
        }

        if (self::$navigationBadgeMemo !== null) {
            return self::$navigationBadgeMemo;
        }

        self::$navigationBadgeMemo = strval(static::getEloquentQuery()->count());

        return self::$navigationBadgeMemo;
    }

    public static function isScopedToTenant(): bool
    {
        return (bool) config('filament-shield.shield_resource.is_scoped_to_tenant', false);
    }

    public static function canGloballySearch(): bool
    {
        return (bool) config('filament-shield.shield_resource.is_globally_searchable', false)
            && count(static::getGloballySearchableAttributes())
            && static::canViewAny();
    }
}
