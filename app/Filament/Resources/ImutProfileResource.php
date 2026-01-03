<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImutProfileResource\Pages;
use App\Filament\Resources\ImutProfileResource\Schema\ImutProfileResourceSchema;
use App\Filament\Resources\ImutProfileResource\Tables\ImutProfileResourceTable;
use App\Models\ImutProfile;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ImutProfileResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ImutProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'force_editable'
        ];
    }

    public static function getLabel(): ?string
    {
        return 'Profil IMUT';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Profil IMUT';
    }

    public static function form(Form $form): Form
    {
        return $form->schema(ImutProfileResourceSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ImutProfileResourceTable::columns())
            ->filters(ImutProfileResourceTable::filters())
            ->actions(ImutProfileResourceTable::actions())
            ->bulkActions(ImutProfileResourceTable::bulkActions());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImutProfiles::route('/'),
            'manage-form-builder' => Pages\ManageFormBuilder::route('/{record:slug}/form-builder'),
            'preview-form' => Pages\FormBuilder::route('/{record:slug}/form-builder/preview'),
            'list-daily-reports' => Pages\ListDailyReports::route('/{record:slug}/daily-reports'),
        ];
    }
}
