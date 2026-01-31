<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\Pages\Helper\FormFields;
use App\Models\ImutData;
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

    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.preview-form-builder';

    public ?array $data = [];
    public ?ImutData $record = null;
    public ?FormTemplate $formTemplate = null;
    public ?array $previewData = [];
    public ?float $complianceScore = null;

    public function mount(ImutData $record): void
    {
        $this->record = $record;

        // Load form template
        $this->formTemplate = FormTemplate::where('imut_data_id', $record->id)->first();

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
        $this->initializePreviewData();
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
            ->schema($this->buildPreviewFormSchema())
            ->statePath('previewData')
            ->live();
    }

    protected function buildPreviewFormSchema(): array
    {
        $fields = [];

        // Form Header Section
        $fields[] = Section::make('Informasi Form')
            ->schema([
                Placeholder::make('form_title')
                    ->content(fn() => new HtmlString('<h2 class="text-xl font-semibold text-gray-900 dark:text-white">' . $this->formTemplate->title . '</h2>')),

                Placeholder::make('form_description')
                    ->content($this->formTemplate->description ?? '')
                    ->visible(fn() => !empty($this->formTemplate->description)),

                Grid::make(3)
                    ->schema([
                        Placeholder::make('compliance_method')
                            ->content('Metode: ' . ucfirst(str_replace('_', ' ', $this->formTemplate->compliance_method))),

                        Placeholder::make('total_fields')
                            ->content('Fields: ' . $this->formTemplate->formFields->count()),

                        Placeholder::make('critical_fields')
                            ->content('Critical: ' . $this->formTemplate->formFields->where('is_critical_field', true)->count()),
                    ])
            ])
            ->collapsible();

        // Dynamic Form Fields Section
        $formFieldsSchema = [];
        $sortedFields = $this->formTemplate->formFields->sortBy('order_index');

        foreach ($sortedFields as $field) {
            $component = FormFields::createFormComponent($field, '', true); // true = isPreview
            if ($component) {
                // Wrap each field in its own Section to improve visual separation
                $formFieldsSchema[] = Section::make($field->field_label)
                    ->schema([
                        $component->label(''),
                    ])
                    ->columns(1);
            }
        }

        $fields[] = Section::make('Preview Form Fields')
            ->description('Preview form sesuai konfigurasi yang telah dibuat. Data yang diisi akan digunakan untuk menghitung compliance score.')
            ->schema($formFieldsSchema)
            ->columns(1);

        // Compliance Calculation Section
        $fields[] = Section::make('Compliance Calculation')
            ->schema([
                Placeholder::make('compliance_calculation')
                    ->content(fn() => $this->generateCompliancePreview())
                    ->columnSpanFull(),
            ]);

        return $fields;
    }

    protected function generateCompliancePreview(): HtmlString
    {
        if (!$this->formTemplate) {
            return new HtmlString('<p class="text-gray-500">Form template tidak tersedia</p>');
        }

        try {
            $currentData = $this->form->getState();
        } catch (\Exception $e) {
            // Handle validation errors by using current preview data
            $currentData = $this->previewData ?? [];
        }

        $compliance = $this->calculateCompliance($currentData);

        $html = '<div class="space-y-4">';

        // Overall Score
        $scoreColor = $compliance['score'] >= 80 ? 'green' : ($compliance['score'] >= 60 ? 'yellow' : 'red');
        $html .= '<div class="p-4 rounded-lg bg-gray-50 dark:bg-slate-800/80">';
        $html .= '<h3 class="text-lg font-semibold mb-2">Overall Compliance Score</h3>';
        $html .= '<div class="text-2xl font-bold text-' . $scoreColor . '-600">' . number_format($compliance['score'], 1) . '%</div>';
        $html .= '<p class="text-sm text-gray-600">' . $compliance['status'] . '</p>';
        $html .= '</div>';

        // Field Breakdown
        $html .= '<div class="p-4 rounded-lg bg-gray-50 dark:bg-slate-800/80">';
        $html .= '<h4 class="font-semibold mb-3">Field Breakdown</h4>';
        $html .= '<div class="space-y-2 text-sm">';

        foreach ($compliance['fields'] as $fieldKey => $fieldData) {
            $field = $this->formTemplate->formFields->where('field_key', $fieldKey)->first();
            if ($field) {
                $weight = $field->compliance_weight;
                $score = $fieldData['score'];
                $maxScore = $weight;
                $percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;

                $html .= '<div class="flex justify-between">';
                $html .= '<span>' . $field->field_label . ($field->is_critical_field ? ' ⚠️' : '') . '</span>';
                $html .= '<span>' . number_format($score, 1) . '/' . $weight . ' (' . number_format($percentage, 1) . '%)</span>';
                $html .= '</div>';
            }
        }

        $html .= '</div></div>';

        // Auto-fail warnings
        if (!empty($compliance['warnings'])) {
            $html .= '<div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200">';
            $html .= '<h4 class="font-semibold text-red-800 mb-2">Warnings</h4>';
            $html .= '<ul class="text-sm text-red-700 space-y-1">';
            foreach ($compliance['warnings'] as $warning) {
                $html .= '<li>• ' . $warning . '</li>';
            }
            $html .= '</ul></div>';
        }

        $html .= '</div>';

        return new HtmlString($html);
    }

    protected function calculateCompliance(array $data): array
    {
        $totalWeight = 0;
        $totalScore = 0;
        $fieldBreakdown = [];
        $warnings = [];
        $autoFail = false;

        foreach ($this->formTemplate->formFields as $field) {
            $fieldValue = $data[$field->field_key] ?? null;
            $fieldScore = 0;

            // Skip if field is not visible due to conditional logic
            if ($field->conditional_logic && !FormFields::isFieldVisible($field, $data)) {
                continue;
            }

            if ($field->compliance_weight > 0) {
                $totalWeight += $field->compliance_weight;

                // Calculate field score based on field type and options
                if (!empty($fieldValue)) {
                    switch ($field->field_type) {
                        case 'single_select':
                            $option = $field->options->where('option_value', $fieldValue)->first();
                            if ($option) {
                                // Simple boolean: is_correct = Pass (full weight), not correct = Fail (0)
                                $fieldScore = ($option->is_correct ?? false) ? $field->compliance_weight : 0;
                            }
                            break;

                        case 'multi_select':
                            if (is_array($fieldValue)) {
                                $correctSelected = 0;
                                $wrongSelected = 0;

                                foreach ($fieldValue as $value) {
                                    $option = $field->options->where('option_value', $value)->first();
                                    if ($option) {
                                        if ($option->is_correct ?? false) {
                                            $correctSelected++;
                                        } else {
                                            $wrongSelected++;
                                        }
                                    }
                                }

                                // Apply boolean rules
                                $complianceRules = $field->compliance_rules ?? [];
                                $minCorrect = $complianceRules['minimum_correct'] ?? 1;
                                $allowWrong = $complianceRules['allow_wrong_selections'] ?? true;

                                // Check if passes rules
                                $passesMinimum = $correctSelected >= $minCorrect;
                                $passesWrongRule = $allowWrong || $wrongSelected == 0;

                                $fieldScore = ($passesMinimum && $passesWrongRule) ? $field->compliance_weight : 0;
                            }
                            break;

                        case 'boolean':
                            if ($field->options->count() > 0) {
                                $option = $field->options->where('option_value', $fieldValue)->first();
                                if ($option) {
                                    $fieldScore = $option->compliance_value * $field->compliance_weight;
                                }
                            } else {
                                $fieldScore = $fieldValue ? $field->compliance_weight : 0;
                            }
                            break;

                        default:
                            $fieldScore = $field->compliance_weight; // Default to full score if filled
                    }
                } else if ($field->is_critical_field && $field->validation_config['required'] ?? false) {
                    $warnings[] = "Critical field '{$field->field_label}' is required but empty";
                    if ($this->formTemplate->auto_fail_on_critical) {
                        $autoFail = true;
                    }
                }

                $totalScore += $fieldScore;
                $fieldBreakdown[$field->field_key] = [
                    'score' => $fieldScore,
                    'weight' => $field->compliance_weight
                ];
            }
        }

        $percentage = $totalWeight > 0 ? ($totalScore / $totalWeight) * 100 : 0;

        if ($autoFail) {
            $percentage = 0;
            $status = 'Auto-Failed (Critical field missing)';
        } else {
            $status = $percentage >= 80 ? 'Compliant' : ($percentage >= 60 ? 'Partially Compliant' : 'Non-Compliant');
        }

        return [
            'score' => $percentage,
            'total_score' => $totalScore,
            'total_weight' => $totalWeight,
            'status' => $status,
            'fields' => $fieldBreakdown,
            'warnings' => $warnings,
            'auto_fail' => $autoFail
        ];
    }

    protected function initializePreviewData(): void
    {
        $this->previewData = [];

        foreach ($this->formTemplate->formFields as $field) {
            // Set default values based on field type and validation
            $defaultValue = null;

            if (($field->validation_config['required'] ?? false)) {
                // Set appropriate default for required fields to prevent validation errors
                switch ($field->field_type) {
                    case 'text':
                        $defaultValue = '';
                        break;
                    case 'number':
                        $defaultValue = 0;
                        break;
                    case 'single_select':
                    case 'boolean':
                        // Leave as null for selects to show placeholder
                        $defaultValue = null;
                        break;
                    case 'multi_select':
                        $defaultValue = [];
                        break;
                    default:
                        $defaultValue = null;
                }
            }

            $this->previewData[$field->field_key] = $defaultValue;
        }
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
        return 'Preview Form: ' . $this->record->title;
    }

    public function getBreadcrumb(): string
    {
        return 'Preview Form';
    }

    public function getBreadcrumbs(): array
    {
        return [
            ImutDataResource::getUrl('index') => 'Data IMUT',
            ImutDataResource::getUrl('edit', ['record' => $this->record]) => $this->record->title,
            ImutDataResource::getUrl('manage-form-builder', ['record' => $this->record]) => 'Konfigurasi Form Laporan Harian',
            '#' => 'Preview Form',
        ];
    }
}
