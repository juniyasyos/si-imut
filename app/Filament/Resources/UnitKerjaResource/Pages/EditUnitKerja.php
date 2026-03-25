<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use App\Filament\Resources\UnitKerjaResource;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\UsersRelationManager;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\ImutDataRelationManager;
use Guava\FilamentModalRelationManagers\Actions\Action\RelationManagerAction;
use Juniyasyos\ManageUnitKerja\Filament\Resources\UnitKerjaResource\Pages\EditUnitKerja as PagesEditUnitKerja;

class EditUnitKerja extends PagesEditUnitKerja
{
    protected static string $resource = UnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RelationManagerAction::make('users')
                ->slideOver()
                ->icon('heroicon-o-user')
                ->record($this->getRecord())
                ->label(__('filament-forms::unit-kerja.actions.attach'))
                ->relationManager(UsersRelationManager::make()),

            RelationManagerAction::make('imutData')
                ->slideOver()
                ->icon('heroicon-o-document-text')
                ->record($this->getRecord())
                ->label('Imut Data')
                ->relationManager(ImutDataRelationManager::make()),
        ];
    }

    // customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
