<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\ImutProfileResource\Pages\ListImutProfiles;
use App\Filament\Resources\ImutProfileResource\Pages;
use App\Filament\Resources\ImutProfileResource\Schema\ImutProfileResourceSchema;
use App\Filament\Resources\ImutProfileResource\Tables\ImutProfileResourceTable;
use App\Models\ImutProfile;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ImutProfileResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ImutProfile::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components(ImutProfileResourceSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ImutProfileResourceTable::columns())
            ->filters(ImutProfileResourceTable::filters())
            ->recordActions(ImutProfileResourceTable::actions())
            ->toolbarActions(ImutProfileResourceTable::bulkActions());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImutProfiles::route('/'),
            // 'manage-form-builder' => Pages\ManageFormBuilder::route('/{record:slug}/form-builder'),
            // 'preview-form' => Pages\FormBuilder::route('/{record:slug}/form-builder/preview'),
            // 'list-daily-reports' => Pages\ListDailyReports::route('/{record:slug}/daily-reports'),
        ];
    }
}
