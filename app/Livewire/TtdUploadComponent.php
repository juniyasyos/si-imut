<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class TtdUploadComponent extends MyProfileComponent
{
    protected string $view = "livewire.ttd-upload-component";

    public array $only = ['ttd_url'];

    public array $data;

    public $user;

    public $userClass;

    public function mount()
    {
        $this->user = auth()->user();
        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only($this->only));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('ttd_url')
                    ->label('Upload Tanda Tangan Digital')
                    ->disk('public')
                    ->directory('ttd')
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                    ->maxSize(2048)
                    ->required(false),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = collect($this->form->getState())->only($this->only)->all();
        $this->user->update($data);

        Notification::make()
            ->success()
            ->title(__('Tanda Tangan Digital berhasil diperbarui'))
            ->send();
    }

    public function render()
    {
        return view('livewire.ttd-upload-component');
    }
}
