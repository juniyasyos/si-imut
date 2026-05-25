<?php

namespace App\Livewire;

use App\Support\StorageFallback;
use Filament\Schemas\Schema;
use Filament\Forms;
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

    // disk yang akan dipakai oleh FileUpload — otomatis fallback ke 'local' bila MinIO tidak dapat diakses
    public string $storageDisk = 's3';

    public function mount()
    {
        $this->user = auth()->user();
        $this->userClass = get_class($this->user);

        // Tentukan disk berdasarkan ketersediaan MinIO
        // gunakan `public` (dapat diakses melalui /storage) sebagai fallback — bukan `local` yang private
        $this->storageDisk = StorageFallback::isS3Available() ? 's3' : 'public';

        $this->form->fill($this->user->only($this->only));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('ttd_url')
                    ->label('Upload Tanda Tangan Digital')
                    ->disk($this->storageDisk)
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
