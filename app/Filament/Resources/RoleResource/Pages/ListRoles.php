<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->disabled(config('iam.role_sync_mode') === 'pull')
                ->visible(config('iam.role_sync_mode') !== 'pull')
                ->icon('heroicon-m-plus'),
        ];
    }
}
