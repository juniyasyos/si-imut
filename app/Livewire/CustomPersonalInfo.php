<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{
    public array $only = ['name', 'email', 'ttd_url'];

    protected function getProfileFormComponents(): array
    {
        return [
            $this->getNameComponent(),
            $this->getEmailComponent(),
        ];
    }

    protected function sendNotification(): void
    {
        Notification::make()
            ->success()
            ->title(__('Personal info updated successfully'))
            ->send();
    }
}
