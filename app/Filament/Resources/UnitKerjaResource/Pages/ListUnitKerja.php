<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use Filament\Actions;
use App\Filament\Resources\UnitKerjaResource;
use Juniyasyos\ManageUnitKerja\Filament\Resources\UnitKerjaResource\Pages\ListUnitKerja as PagesListUnitKerja;

class ListUnitKerja extends PagesListUnitKerja
{
    protected static string $resource = UnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (UnitKerjaResource::isCrudAllowed()) {
            $actions[] = Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-m-plus');
        }

        if (UnitKerjaResource::isSyncActive()) {
            $actions[] = Actions\Action::make('provisionFromCenter')
                ->label('Provision from App Center')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('secondary')
                ->action('provisionFromCenter')
                ->requiresConfirmation()
                ->modalHeading('Provision from App Center');
        }

        return $actions;
    }

    public function provisionFromCenter(): void
    {
        if (!UnitKerjaResource::isSyncActive()) {
            $this->notify('danger', 'Sync belum diaktifkan.');

            return;
        }

        // TODO: implementasi provisioning asli dari app center.
        // Saat ini placeholder (developer dapat override dengan logika nyata di app utama).
        $this->notify('success', 'Provisioning dari app center dijalankan (placeholder).');
    }
}
