<?php

namespace App\Filament\Resources\ImutBenchmarkingResource\Pages;

use App\Filament\Resources\ImutBenchmarkingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImutBenchmarkings extends ListRecords
{
    protected static string $resource = ImutBenchmarkingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Benchmark'),
        ];
    }

    public function getTitle(): string
    {
        return 'Benchmark Management';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ImutBenchmarkingResource\Widgets\BenchmarkOverviewWidget::class,
        ];
    }
}
