<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Services\Filament\ImutDataFilamentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateImutData extends CreateRecord
{
    protected static string $resource = ImutDataResource::class;
    protected static bool $canCreateAnother = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->record->slug,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(ImutDataFilamentService::class)->createImutDataWithUnitKerja($data);
    }
}
