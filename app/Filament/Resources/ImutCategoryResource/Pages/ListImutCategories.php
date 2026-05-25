<?php

namespace App\Filament\Resources\ImutCategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use App\Models\ImutCategory;
use Illuminate\Support\Facades\Gate;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ImutCategoryResource;

class ListImutCategories extends ListRecords
{
    protected static string $resource = ImutCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Data')
                ->visible(fn() => Gate::allows('create_imut::category', ImutCategory::class))
                ->icon('heroicon-m-plus'),
        ];
    }
}
