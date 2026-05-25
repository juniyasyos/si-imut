<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
     * @return Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        $repository = app(ImutDataRepositoryInterface::class);

        $user = Auth::user();

        $unitKerjaIds = $user->can('attach_imut_data_to_unit_kerja_unit::kerja')
            ? ($data['unitKerjaIds'] ?? [])
            : $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        return $repository->createWithUnitKerjas($data, $unitKerjaIds, $user->id);
    }
}