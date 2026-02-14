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
            FileUpload::make('ttd_url')
                ->label('Upload Tanda Tangan Digital')
                ->disk('public')
                ->directory('ttd')
                ->image()
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                ->maxSize(2048)
                ->required(false),
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
