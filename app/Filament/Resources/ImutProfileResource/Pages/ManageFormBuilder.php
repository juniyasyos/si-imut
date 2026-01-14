<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutProfileResource;
use App\Models\ImutProfile;
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
use Illuminate\Support\Facades\Log;

class ManageFormBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ImutProfileResource::class;

    protected static string $view = 'filament.resources.imut-profile-resource.pages.manage-form-builder';

    public ?array $data = [];
    public ?ImutProfile $record = null;
    public bool $autoSaveEnabled = true;

    public function mount(ImutProfile $record): void
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

    public function autoSave(): void
    {
        if (!$this->autoSaveEnabled) {
            return;
        }

        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $formPersistenceService = new FormPersistenceService();
            $formPersistenceService->saveFormData($this->record, $data);
            $formPersistenceService->calculateAndUpdateCompliance($this->record);

            DB::commit();

            // Silent save - no notification to avoid disrupting user
        } catch (\Exception $e) {
            DB::rollBack();
            // Silent fail - only log error, don't show notification
            Log::warning('Auto-save failed: ' . $e->getMessage());
        }
    }

    public function preview(): void
    {
        // Save current form data before redirecting to preview
        try {
            $data = $this->form->getState();
            $formPersistenceService = new FormPersistenceService();
            $formPersistenceService->saveFormData($this->record, $data);
        } catch (\Exception $e) {
            // Continue to preview even if save fails
        }

        // Redirect to preview page
        $this->redirect(ImutDataResource::getUrl('preview-form', [
            'imutDataSlug' => $this->record->imutData->slug,
            'record' => $this->record->slug,
        ]));
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

    protected function getFooterWidgets(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'autoSaveScript' => "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Auto-save every 20 seconds
                        setInterval(function() {
                            if (window.Livewire) {
                                try {
                                    Livewire.find('" . $this->getId() . "').call('autoSave');
                                } catch (e) {
                                    console.log('Auto-save skipped:', e.message);
                                }
                            }
                        }, 20000); // 20 seconds

                        console.log('Auto-save initialized - saving every 20 seconds');
                    });
                </script>
            "
        ]);
    }
}
