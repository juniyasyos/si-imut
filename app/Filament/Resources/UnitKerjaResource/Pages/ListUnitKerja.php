<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use Filament\Actions;
use App\Filament\Resources\UnitKerjaResource;
use Juniyasyos\ManageUnitKerja\Filament\Resources\UnitKerjaResource\Pages\ListUnitKerja as PagesListUnitKerja;

class ListUnitKerja extends PagesListUnitKerja
{
    protected static string $resource = UnitKerjaResource::class;

    // public function mount(): void
    // {
    //     parent::mount();

    //     $user = Auth::user();

    //     // Menampilkan semua permission user yang aktif
    //     $permissions = $user->getAllPermissions()->pluck('name');

    //     dd($permissions); // Lihat daftar permission yang dimiliki
    // }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-m-plus'),
        ];
    }
}
