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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class ManageFormBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ImutProfileResource::class;

    protected static string $view = 'filament.resources.imut-profile-resource.pages.manage-form-builder';

    public ?array $data = [];
    public ?ImutProfile $record = null;
    public bool $autoSaveEnabled = false;
    public bool $hasExistingResponses = false;
    public int $responseCount = 0;
    public bool $canForceUpdate = false;

    public function mount(ImutProfile $record): void
    {
        $this->record = $record;
        $this->canForceUpdate = Gate::allows('updateFormWithExistingResponses', $this->record);

        $formDataService = new FormDataService();
        $this->data = $formDataService->loadFormData($record);
        $this->form->fill($this->data);

        // Check if form has existing responses
        $formPersistenceService = new FormPersistenceService();
        $this->hasExistingResponses = $formPersistenceService->hasExistingResponses($record);
        $this->responseCount = $formPersistenceService->getResponseCount($record);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(FormSchemaBuilder::buildFormSchema())
            ->statePath('data')
            ->model($this->record)
            ->disabled($this->hasExistingResponses && !$this->canForceUpdate);
    }

    protected function getHeaderActions(): array
    {
        if ($this->hasExistingResponses && !$this->canForceUpdate) {
            return [
                Action::make('locked')
                    ->label('Form Sudah Memiliki Data Response')
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->disabled(),

                Action::make('preview')
                    ->label('Preview Form')
                    ->icon('heroicon-o-eye')
                    ->action('preview')
                    ->color('info'),
            ];
        }

        $saveAction = Action::make('save')
            ->label('Simpan Form')
            ->icon('heroicon-o-check')
            ->color('success')
            ->action('performSave');

        if ($this->hasExistingResponses && $this->canForceUpdate) {
            $saveAction->requiresConfirmation()
                ->modalHeading('Konfirmasi Perubahan Form (Super Admin)')
                ->modalDescription("Form template ini sudah memiliki {$this->responseCount} data respons harian. Sebagai super admin, Anda dapat memaksa update, yang akan menghapus semua data respons yang ada. Apakah Anda yakin ingin melanjutkan?")
                ->modalSubmitActionLabel('Ya, Hapus Data dan Simpan')
                ->modalCancelActionLabel('Batal');
        }

        return [
            $saveAction,

            Action::make('preview')
                ->label('Preview Form')
                ->icon('heroicon-o-eye')
                ->action('preview')
                ->color('info'),
        ];
    }

    public function performSave(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $formPersistenceService = new FormPersistenceService();

            if ($this->hasExistingResponses && $this->canForceUpdate) {
                $formPersistenceService->deleteResponses($this->record);
            }

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
        // Save current form data before redirecting to preview (only if no existing responses)
        if (!$this->hasExistingResponses) {
            try {
                $data = $this->form->getState();
                $formPersistenceService = new FormPersistenceService();
                $formPersistenceService->saveFormData($this->record, $data);
            } catch (\Exception $e) {
                // Continue to preview even if save fails
            }
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
            // Auto-save disabled
        ]);
    }
}
