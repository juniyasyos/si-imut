<?php

namespace App\Filament\Resources\MediaCustomResource\Pages;

use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Gate;
use App\Filament\Resources\MediaCustomResource;
use Filament\Actions;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages\EditMedia;

class EditMediaCustom extends EditMedia
{
    protected static string $resource = MediaCustomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => Gate::any(['delete_media::custom'])),
        ];
    }
}
