<?php

namespace App\Filament\Resources\ImutBenchmarkingResource\Pages;

use App\Filament\Resources\ImutBenchmarkingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateImutBenchmarking extends CreateRecord
{
    protected static string $resource = ImutBenchmarkingResource::class;

    public function getTitle(): string
    {
        return 'Tambah Benchmark Baru';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
