<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use Filament\Schemas\Schema;
use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutProfileResource;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FormFields;
use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Services\FormBuilder\FormDataService;
use App\Services\DynamicForm\DynamicFormService;
use App\Services\DynamicForm\ComplianceCalculatorService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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

    protected string $view = 'filament.resources.imut-profile-resource.pages.preview-form-builder';

    public ?array $data = [];
    public ?ImutProfile $record = null;
    public ?FormTemplate $formTemplate = null;
    public ?array $previewData = [];
    public ?float $complianceScore = null;

    public function mount(ImutProfile $record): void
    {
        $this->record = $record;

        // Load active form template
        $this->formTemplate = $record->activeFormTemplate;

        if (!$this->formTemplate) {
            Notification::make()
                ->title('Form Template Tidak Ditemukan')
                ->body('Silakan buat form template terlebih dahulu di Form Builder.')
                ->warning()
                ->send();

            $this->redirect(ImutDataResource::getUrl('manage-form-builder', [
                'imutDataSlug' => $record->imutData->slug,
                'record' => $record->slug,
                'templateId' => $this->formTemplate?->id,
            ]));
            return;
        }

        // Initialize empty form data
        $this->previewData = DynamicFormService::initializeFormData($this->formTemplate);
        $this->form->fill($this->previewData);
    }

    public function form(Schema $schema): Schema
    {
        if (!$this->formTemplate) {
            return $schema->components([
                Placeholder::make('no_template')
                    ->content('Form template tidak ditemukan.')
            ]);
        }

        return $schema
            ->components(DynamicFormService::buildFormSchema($this->formTemplate, true, true))
            ->statePath('previewData')
            ->live();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_builder')
                ->label('Kembali ke Form Builder')
                ->icon('heroicon-o-arrow-left')
                ->url(fn() => ImutDataResource::getUrl('manage-form-builder', [
                    'imutDataSlug' => $this->record->imutData->slug,
                    'record' => $this->record->slug,
                    'templateId' => $this->formTemplate?->id,
                ]))
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
            ImutDataResource::getUrl('index') => 'Daftar Data IMUT',
            ImutDataResource::getUrl('edit', ['record' => $this->record->imutData->slug]) => $this->record->imutData->title,
            ImutDataResource::getUrl('manage-form-builder', [
                'imutDataSlug' => $this->record->imutData->slug,
                'record' => $this->record->slug,
                'templateId' => $this->formTemplate?->id,
            ]) => 'Konfigurasi Form Laporan Harian',
            'Preview Form: ' . $this->record->version,
        ];
    }
}
