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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageFormBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.manage-form-builder';

    public ?array $data = [];
    public ?ImutData $record = null;
    public bool $autoSaveEnabled = false;

    public function mount(ImutData $record): void
    {
        $this->record = $record;
        $profile = $record->latestProfile;

        if (!$profile) {
            // Handle case where no profile exists
            $this->data = [];
            return;
        }

        $formDataService = new FormDataService();
        $this->data = $formDataService->loadFormData($profile);
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
                ->visible(function (Model $record) {
                    return Auth::user()?->can('delete_imut::profile') && $record->imutData->created_by === Auth::id();
                })
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
        $profile = $this->record->latestProfile;

        if (!$profile) {
            Notification::make()
                ->title('Tidak dapat menyimpan form')
                ->body('Tidak ada profil IMUT yang aktif untuk data ini.')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            $formPersistenceService = new FormPersistenceService();
            $formPersistenceService->saveFormData($profile, $data);
            $formPersistenceService->calculateAndUpdateCompliance($profile);

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
        $profile = $this->record->latestProfile;

        if (!$profile) {
            return; // Silent fail if no profile
        }

        try {
            DB::beginTransaction();

            $formPersistenceService = new FormPersistenceService();
            $formPersistenceService->saveFormData($profile, $data);
            $formPersistenceService->calculateAndUpdateCompliance($profile);

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
            $profile = $this->record->latestProfile;

            if ($profile) {
                $formPersistenceService = new FormPersistenceService();
                $formPersistenceService->saveFormData($profile, $data);
            }
        } catch (\Exception $e) {
            // Continue to preview even if save fails
        }

        // Redirect to preview page
        $this->redirect(static::getResource()::getUrl('preview-form', ['record' => $this->record]));
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
