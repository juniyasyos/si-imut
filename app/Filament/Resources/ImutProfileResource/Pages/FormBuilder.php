<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use App\Filament\Resources\ImutProfileResource;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FormFields;
use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Services\FormBuilder\FormDataService;
use App\Services\DynamicForm\DynamicFormService;
use App\Services\DynamicForm\ComplianceCalculatorService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class FormBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ImutProfileResource::class;

    protected static string $view = 'filament.resources.imut-profile-resource.pages.preview-form-builder';

    public ?array $data = [];
    public ?ImutProfile $record = null;
    public ?FormTemplate $formTemplate = null;
    public ?array $previewData = [];
    public ?float $complianceScore = null;

    public function mount(ImutProfile $record): void
    {
        $this->record = $record;

        // Load form template
        $this->formTemplate = FormTemplate::where('imut_profile_id', $record->id)->first();

        if (!$this->formTemplate) {
            Notification::make()
                ->title('Form Template Tidak Ditemukan')
                ->body('Silakan buat form template terlebih dahulu di Form Builder.')
                ->warning()
                ->send();

            $this->redirect(static::getResource()::getUrl('manage-form-builder', ['record' => $record]));
            return;
        }

        // Initialize empty form data
        $this->previewData = DynamicFormService::initializeFormData($this->formTemplate);
        $this->form->fill($this->previewData);
    }

    public function form(Form $form): Form
    {
        if (!$this->formTemplate) {
            return $form->schema([
                Placeholder::make('no_template')
                    ->content('Form template tidak ditemukan.')
            ]);
        }

        return $form
            ->schema(DynamicFormService::buildFormSchema($this->formTemplate, true, true))
            ->statePath('previewData')
            ->live();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_builder')
                ->label('Kembali ke Form Builder')
                ->icon('heroicon-o-arrow-left')
                ->url(fn() => static::getResource()::getUrl('manage-form-builder', ['record' => $this->record]))
                ->color('gray'),
        ];
    }

    public function getTitle(): string
    {
        return 'Preview Form: ' . $this->record->version;
    }

    public function getBreadcrumb(): string
    {
        return 'Preview Form';
    }

    public function getBreadcrumbs(): array
    {
        return [
            ImutProfileResource::getUrl('index') => 'Profil IMUT',
            ImutProfileResource::getUrl('manage-form-builder', ['record' => $this->record]) => 'Konfigurasi Form Laporan Harian',
            '#' => 'Preview Form',
        ];
    }
}
