<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use App\Filament\Resources\ImutProfileResource;
use App\Filament\Resources\ImutDataResource\Pages\Helper\FormFields;
use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Services\FormBuilder\FormDataService;
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
                ->title('Form belum dikonfigurasi')
                ->body('Silakan konfigurasi form terlebih dahulu sebelum melihat preview.')
                ->warning()
                ->send();

            $this->redirect(static::getResource()::getUrl('manage-form-builder', ['record' => $record]));
            return;
        }

        // Fill form with empty data to show the structure
        $this->form->fill([]);
    }

    public function form(Form $form): Form
    {
        if (!$this->formTemplate) {
            return $form->schema([
                Placeholder::make('no_form')
                    ->label('Form Tidak Tersedia')
                    ->content('Form belum dikonfigurasi untuk profil ini.')
            ]);
        }

        $schema = [];

        // Add form title and description
        $schema[] = Section::make($this->formTemplate->title)
            ->description($this->formTemplate->description)
            ->schema($this->buildFormFields());

        return $form->schema($schema);
    }

    private function buildFormFields(): array
    {
        $fields = [];

        foreach ($this->formTemplate->formFields()->orderBy('order_index')->get() as $field) {
            $component = $this->createFieldComponent($field);
            if ($component) {
                $fields[] = $component;
            }
        }

        return $fields;
    }

    private function createFieldComponent($field)
    {
        switch ($field->field_type) {
            case 'text':
                return TextInput::make($field->field_key)
                    ->label($field->field_label)
                    ->placeholder($field->field_description)
                    ->disabled();

            case 'textarea':
                return Textarea::make($field->field_key)
                    ->label($field->field_label)
                    ->placeholder($field->field_description)
                    ->rows(3)
                    ->disabled();

            case 'select':
                $options = [];
                foreach ($field->options as $option) {
                    $options[$option->option_value] = $option->option_text;
                }

                return Select::make($field->field_key)
                    ->label($field->field_label)
                    ->options($options)
                    ->placeholder($field->field_description)
                    ->disabled();

            case 'radio':
                $options = [];
                foreach ($field->options as $option) {
                    $options[$option->option_value] = $option->option_text;
                }

                return Radio::make($field->field_key)
                    ->label($field->field_label)
                    ->options($options)
                    ->disabled();

            case 'checkbox':
                $options = [];
                foreach ($field->options as $option) {
                    $options[$option->option_value] = $option->option_text;
                }

                return CheckboxList::make($field->field_key)
                    ->label($field->field_label)
                    ->options($options)
                    ->disabled();

            case 'toggle':
                return Toggle::make($field->field_key)
                    ->label($field->field_label)
                    ->disabled();

            default:
                return TextInput::make($field->field_key)
                    ->label($field->field_label)
                    ->placeholder($field->field_description)
                    ->disabled();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_builder')
                ->label('Kembali ke Builder')
                ->icon('heroicon-o-arrow-left')
                ->url(fn() => static::getResource()::getUrl('manage-form-builder', ['record' => $this->record]))
                ->color('gray'),

            Action::make('test_form')
                ->label('Test Form')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action('testForm'),
        ];
    }

    public function testForm(): void
    {
        // Calculate compliance score based on current form data
        $this->complianceScore = $this->formTemplate->calculateCompliance($this->data);

        Notification::make()
            ->title('Form Test Completed')
            ->body("Compliance Score: " . round($this->complianceScore * 100, 2) . "%")
            ->success()
            ->send();
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Kembali',
            ImutProfileResource::getUrl('manage-form-builder', ['record' => $this->record]) => 'Konfigurasi Form Laporan Harian',
            '#' => 'Preview Form',
        ];
    }

    public function getTitle(): string
    {
        return 'Preview Form Laporan Harian';
    }

    public function getHeading(): string
    {
        return 'Preview Form: ' . ($this->record->version ?? 'Profil IMUT');
    }
}
