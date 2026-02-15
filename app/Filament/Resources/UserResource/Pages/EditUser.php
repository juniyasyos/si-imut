<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Guava\FilamentModalRelationManagers\Actions\Action\RelationManagerAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()->record($this->getRecord()),
            RelationManagerAction::make('activities')
                ->slideOver()
                ->label('Activity Log')
                ->icon('heroicon-o-clock')
                ->record($this->getRecord())
                ->color('gray')
                ->relationManager(ActivitylogRelationManager::make()),
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
