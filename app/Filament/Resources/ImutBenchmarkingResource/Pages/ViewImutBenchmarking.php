<?php

namespace App\Filament\Resources\ImutBenchmarkingResource\Pages;

use App\Filament\Resources\ImutBenchmarkingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewImutBenchmarking extends ViewRecord
{
    protected static string $resource = ImutBenchmarkingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Detail Benchmark';
    }
}
