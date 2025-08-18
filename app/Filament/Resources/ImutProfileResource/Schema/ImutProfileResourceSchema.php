<?php

namespace App\Filament\Resources\ImutProfileResource\Schema;

use App\Filament\Forms\ImutProfileForm;
use App\Filament\Resources\ImutProfileResource;

class ImutProfileResourceSchema extends ImutProfileResource
{
    public static function make(): array
    {
        return ImutProfileForm::make();
    }
}

