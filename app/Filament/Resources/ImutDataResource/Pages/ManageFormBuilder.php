<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Models\ImutData;
use App\Services\FormBuilder\FormDataService;
use App\Services\FormBuilder\FormSchemaBuilder;
use App\Services\FormBuilder\FormPersistenceService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class ManageFormBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.manage-form-builder';

    public ?array $data = [];
    public ?ImutData $record = null;

    public function mount(ImutData $record): void
    {
        $this->record = $record;
        $formDataService = new FormDataService();
        $this->data = $formDataService->loadFormData($record);
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(FormSchemaBuilder::buildFormSchema())
            ->statePath('data')
            ->model($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Form')
                ->icon('heroicon-o-check')
                ->action('save')
                ->color('success'),

            Action::make('preview')
                ->label('Preview Form')
                ->icon('heroicon-o-eye')
                ->action('preview')
                ->color('info'),

            Action::make('calculate_compliance')
                ->label('Hitung Compliance')
                ->icon('heroicon-o-calculator')
                ->action('calculateCompliance')
                ->color('warning'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $formPersistenceService = new FormPersistenceService();
            $formPersistenceService->saveFormData($this->record, $data);
            $formPersistenceService->calculateAndUpdateCompliance($this->record);

            DB::commit();

            Notification::make()
                ->title('Form berhasil disimpan!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Gagal menyimpan form')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function preview(): void
    {
        $data = $this->form->getState();

        // Generate preview logic here
        Notification::make()
            ->title('Preview Form')
            ->body('Fitur preview akan segera tersedia')
            ->info()
            ->send();
    }

    public function calculateCompliance(): void
    {
        try {
            $formPersistenceService = new FormPersistenceService();
            $formPersistenceService->calculateAndUpdateCompliance($this->record);

            Notification::make()
                ->title('Compliance berhasil dihitung ulang')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menghitung compliance')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getAvailableFields(): array
    {
        $formDataService = new FormDataService();
        return $formDataService->getAvailableFields($this->data);
    }

    public function getFieldOptions(?string $fieldKey): array
    {
        $formDataService = new FormDataService();
        return $formDataService->getFieldOptions($this->data, $fieldKey);
    }
}
