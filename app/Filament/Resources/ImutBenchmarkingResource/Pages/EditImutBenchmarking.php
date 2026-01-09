<?php

namespace App\Filament\Resources\ImutBenchmarkingResource\Pages;

use App\Filament\Resources\ImutBenchmarkingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImutBenchmarking extends EditRecord
{
    protected static string $resource = ImutBenchmarkingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Benchmark';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
