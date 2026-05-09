<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutProfileResource;
use App\Models\ImutProfile;
use App\Models\DailyReportResponse;
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
use Illuminate\Support\Facades\Gate;

class ManageFormBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ImutProfileResource::class;

    protected static string $view = 'filament.resources.imut-profile-resource.pages.manage-form-builder';

    public ?array $data = [];
    public ?ImutProfile $record = null;
    public ?\App\Models\FormTemplate $formTemplate = null;
    public ?int $selectedTemplateId = null;
    public bool $autoSaveEnabled = false;
    public bool $hasExistingResponses = false;
    public int $responseCount = 0;
    public bool $canForceUpdate = false;
    public ?string $currentVersion = null;
    public int $totalVersions = 0;

    public function mount(ImutProfile $record, ?int $templateId = null): void
    {
        $this->record = $record;
        $this->canForceUpdate = Gate::allows('updateFormWithExistingResponses', $this->record);

        // Check both path parameter and query parameter for template selection
        // Path parameter takes precedence: /form-builder/349
        // Query parameter fallback: ?templateId=349 (for backward compatibility)
        $requestedTemplateId = $templateId ?? request()->query('templateId');

        // Determine which template to load
        if ($requestedTemplateId) {
            $this->formTemplate = \App\Models\FormTemplate::where('id', $requestedTemplateId)
                ->where('imut_profile_id', $record->id)
                ->first();
            $this->selectedTemplateId = $requestedTemplateId;
        }

        // Fallback to active template if not found or not specified
        if (!$this->formTemplate) {
            $this->formTemplate = $record->activeFormTemplate;
            $this->selectedTemplateId = $this->formTemplate?->id;
        }

        // Load form data from selected template
        $formDataService = new FormDataService();
        $this->data = $formDataService->loadFormData($record, $this->selectedTemplateId);
        $this->form->fill($this->data);

        // Check if selected template has existing responses
        $formPersistenceService = new FormPersistenceService();
        if ($this->formTemplate) {
            $this->hasExistingResponses = $formPersistenceService->hasExistingResponsesForTemplate($this->formTemplate);
            $this->responseCount = $formPersistenceService->getResponseCountForTemplate($this->formTemplate);
        }

        // Get version information
        $this->currentVersion = $this->formTemplate?->version ?? 'No Template';
        $this->totalVersions = $record->formTemplateVersions()->count();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(FormSchemaBuilder::buildFormSchema($this->hasExistingResponses && !$this->canForceUpdate))
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

        return [
            Action::make('versionInfo')
                ->label("Version: {$this->currentVersion} ({$this->totalVersions} total)")
                ->icon('heroicon-o-information-circle')
                ->color('gray')
                ->disabled(),

            Action::make('reset')
                ->label('Reset Form (Destruktif)')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->visible(fn() => $this->canForceUpdate)
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalHeading('Reset Struktur Form?')
                ->modalDescription('Apakah Anda yakin ingin mereset struktur form ini? Data yang sudah ada akan hilang.')
                ->modalSubmitActionLabel('Ya, Reset Sekarang')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    $this->performReset();
                }),

            Action::make('deleteResponses')
                ->label('Hapus Semua Responses')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn() => $this->canForceUpdate && $this->hasExistingResponses)
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalHeading('Hapus Semua Data Respons?')
                ->modalDescription('Apakah Anda yakin ingin menghapus semua data respons dari form ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus Permanen')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    $this->performDeleteResponses();
                }),

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

            // NOTE: Do NOT delete existing responses during a normal save.
            // Reset of the form template and deletion of responses are
            // handled by separate explicit actions (Reset Form / Hapus Responses).

            if ($this->formTemplate) {
                // Save to specific template without activating it
                $formPersistenceService->saveFormDataToTemplate($this->formTemplate, $data);

                // Calculate compliance for the profile
                $formPersistenceService->calculateAndUpdateCompliance($this->record);
            }

            DB::commit();

            // Refresh version information after save
            $this->refreshVersionInfo();

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

            if ($this->formTemplate) {
                $formPersistenceService->saveFormDataToTemplate($this->formTemplate, $data);
                $formPersistenceService->calculateAndUpdateCompliance($this->record);
            }

            DB::commit();

            // Silent save - no notification to avoid disrupting user
        } catch (\Exception $e) {
            DB::rollBack();
            // Silent fail - only log error, don't show notification
            Log::warning('Auto-save failed: ' . $e->getMessage());
        }
    }

    /**
     * Refresh version information after operations
     */
    private function refreshVersionInfo(): void
    {
        $this->record->refresh();
        // Reload selected template
        if ($this->selectedTemplateId) {
            $this->formTemplate = \App\Models\FormTemplate::find($this->selectedTemplateId);
        } else {
            $this->formTemplate = $this->record->activeFormTemplate;
            $this->selectedTemplateId = $this->formTemplate?->id;
        }

        $this->currentVersion = $this->formTemplate?->version ?? 'No Template';
        $this->totalVersions = $this->record->formTemplateVersions()->count();

        // Also refresh response count for selected template
        if ($this->formTemplate) {
            $formPersistenceService = new FormPersistenceService();
            $this->hasExistingResponses = $formPersistenceService->hasExistingResponsesForTemplate($this->formTemplate);
            $this->responseCount = $formPersistenceService->getResponseCountForTemplate($this->formTemplate);
        }
    }

    /**
     * Perform a destructive reset: remove form fields only.
     * Visible only to users with permission (Super Admin).
     */
    public function performReset(): void
    {
        if (! $this->canForceUpdate) {
            Notification::make()->title('Akses ditolak')->danger()->send();
            return;
        }

        try {
            DB::beginTransaction();

            // Remove form fields from selected template only
            if ($this->formTemplate) {
                $this->formTemplate->formFields()->delete();
                $this->formTemplate->update(['scoring_config' => null]);
            }

            DB::commit();

            // Refresh UI state
            $this->data = (new FormDataService())->loadFormData($this->record, $this->selectedTemplateId);
            $this->form->fill($this->data);
            $this->refreshVersionInfo();

            Notification::make()->title('Reset berhasil')->success()->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->title('Reset gagal')->body($e->getMessage())->danger()->send();
        }
    }
    public function preview(): void
    {
        // Save current form data before redirecting to preview (only if no existing responses)
        if (!$this->hasExistingResponses && $this->formTemplate) {
            try {
                $data = $this->form->getState();
                $formPersistenceService = new FormPersistenceService();
                $formPersistenceService->saveFormDataToTemplate($this->formTemplate, $data);
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

    /**
     * Delete all stored responses but keep the form template intact.
     */
    public function performDeleteResponses(): void
    {
        if (! $this->canForceUpdate) {
            Notification::make()->title('Akses ditolak')->danger()->send();
            return;
        }

        try {
            DB::beginTransaction();

            // Delete responses only for selected template
            if ($this->formTemplate) {
                DailyReportResponse::where('form_template_id', $this->formTemplate->id)->delete();
            }

            DB::commit();

            // Refresh version information
            $this->refreshVersionInfo();

            Notification::make()->title('Semua respons dihapus')->success()->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->title('Penghapusan respons gagal')->body($e->getMessage())->danger()->send();
        }
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
            'currentVersion' => $this->currentVersion,
            'totalVersions' => $this->totalVersions,
            'hasExistingResponses' => $this->hasExistingResponses,
            'responseCount' => $this->responseCount,
            'canForceUpdate' => $this->canForceUpdate,
        ]);
    }
}
