<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImutPenilaianResource\Pages;
use App\Filament\Resources\ImutPenilaianResource\Schema\ImutPenilaianResourceSchema;
use App\Domains\Imut\Models\ImutPenilaian;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class ImutPenilaianResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ImutPenilaian::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'update',
            'delete',
            //
            'update_numerator_denominator',
            'update_profile_penilaian',
            'create_recommendation_penilaian',
            'force_editable'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(ImutPenilaianResourceSchema::make());
    }

    public static function getPages(): array
    {
        return [
            'edit' => Pages\EditImutPenilaian::route('/'),
        ];
    }
}

