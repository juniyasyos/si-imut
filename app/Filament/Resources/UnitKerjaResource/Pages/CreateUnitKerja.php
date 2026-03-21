<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use App\Filament\Resources\UnitKerjaResource;
use Juniyasyos\ManageUnitKerja\Filament\Resources\UnitKerjaResource\Pages\CreateUnitKerja as PagesCreateUnitKerja;

class CreateUnitKerja extends PagesCreateUnitKerja
{
    protected static string $resource = UnitKerjaResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->slug]);
    }
}