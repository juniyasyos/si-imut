<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitKerjaResource\Pages;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\ImutDataRelationManager;
use App\Filament\Resources\UnitKerjaResource\Schema\UnitKerjaResourceSchema;
use App\Filament\Resources\UnitKerjaResource\Tables\UnitKerjaResourceTable;
use App\Models\UnitKerja;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UnitKerjaResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UnitKerja::class;

    protected static ?string $slug = 'unit-kerjas';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

    public static function form(Form $form): Form
    {
        return $form->schema(UnitKerjaResourceSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns(UnitKerjaResourceTable::columns())
            ->filters(UnitKerjaResourceTable::filters())
            ->headerActions(UnitKerjaResourceTable::headerActions())
            ->actions(UnitKerjaResourceTable::actions())
            ->bulkActions(UnitKerjaResourceTable::bulkActions());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnitKerja::route('/'),
            'create' => Pages\CreateUnitKerja::route('/create'),
            'edit' => Pages\EditUnitKerja::route('/{record:slug}/edit'),
        ];
    }
}
