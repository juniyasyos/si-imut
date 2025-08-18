<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImutCategoryResource\Pages;
use App\Filament\Resources\ImutCategoryResource\Schema\ImutCategoryResourceSchema;
use App\Filament\Resources\ImutCategoryResource\Tables\ImutCategoryResourceTable;
use App\Models\ImutCategory;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImutCategoryResource extends Resource implements HasShieldPermissions
{
    use \App\Traits\HasActiveIcon;
    protected static ?string $model = ImutCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?int $navigationSort = 2;

    public static function getPermissionPrefixes(): array
    {
        return [
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
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['category_name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->category_name ?? '';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'ID' => $record->id,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getLabel(): ?string
    {
        return __('filament-forms::imut-category.navigation.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-forms::imut-category.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::imut-category.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(ImutCategoryResourceSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ImutCategoryResourceTable::columns())
            ->filters(ImutCategoryResourceTable::filters())
            ->actions(ImutCategoryResourceTable::actions())
            ->bulkActions(ImutCategoryResourceTable::bulkActions());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImutCategories::route('/'),
            'create' => Pages\CreateImutCategory::route('/create'),
            'edit' => Pages\EditImutCategory::route('/{record}/edit'),
        ];
    }
}
