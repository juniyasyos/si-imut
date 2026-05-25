<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\UnitKerjaResource\Pages\ListUnitKerja;
use App\Filament\Resources\UnitKerjaResource\Pages\CreateUnitKerja;
use App\Filament\Resources\UnitKerjaResource\Pages\EditUnitKerja;
use App\Filament\Resources\UnitKerjaResource\Pages;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\ImutDataRelationManager;
use App\Filament\Resources\UnitKerjaResource\Schema\UnitKerjaResourceSchema;
use App\Filament\Resources\UnitKerjaResource\Tables\UnitKerjaResourceTable;
use App\Models\UnitKerja;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UnitKerjaResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UnitKerja::class;

    protected static ?string $slug = 'unit-kerjas';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPermissionPrefixes(): array
    {
        return [
            // Default permissions
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'attach_user_to_unit_kerja',
            'attach_imut_data_to_unit_kerja'
        ];
    }


    public static function getGloballySearchableAttributes(): array
    {
        return ['unit_name'];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->unit_name;
    }

    public static function getLabel(): ?string
    {
        return __('filament-forms::unit-kerja.navigation.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-forms::unit-kerja.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::unit-kerja.navigation.group');
    }

    public static function isCrudAllowed(): bool
    {
        return (bool) config('iam.sync_unit_kerja', true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(UnitKerjaResourceSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(UnitKerjaResourceTable::columns())
            ->filters(UnitKerjaResourceTable::filters())
            ->headerActions(UnitKerjaResourceTable::headerActions())
            ->recordActions(UnitKerjaResourceTable::actions())
            ->toolbarActions(UnitKerjaResourceTable::bulkActions());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnitKerja::route('/'),
            'create' => CreateUnitKerja::route('/create'),
            'edit' => EditUnitKerja::route('/{record:slug}/edit'),
        ];
    }
}
