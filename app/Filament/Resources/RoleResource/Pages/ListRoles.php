<?php

namespace App\Filament\Resources\RoleResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Data')
                ->visible(!(env('USE_SSO') && env('IAM_ENABLED')) && config('iam.role_sync_mode') !== 'pull')
                ->icon('heroicon-m-plus'),
        ];
    }
}
