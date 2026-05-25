<?php

namespace App\Services\DynamicForm;

use Filament\Schemas\Components\Section;
use Exception;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FormFields;
use App\Models\FormTemplate;
use App\View\Components\ComplianceDisplay;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;

class DynamicFormService
{
    /**
     * Build form schema based on FormTemplate
     */
    public static function buildFormSchema(FormTemplate $formTemplate, bool $includeHeader = true, bool $includeCompliance = false): array
    {
        $fields = [];

        // Dynamic Form Fields Section
        $formFieldsSchema = [];
        $sortedFields = $formTemplate->formFields->sortBy('order_index');

        foreach ($sortedFields as $field) {
            $component = FormFields::createFormComponent($field);
            if ($component) {
                // Wrap each field in its own Section to improve visual separation
                $formFieldsSchema[] = Section::make($field->field_label)
                    ->schema([
                        $component->label(''),
                    ])
                    ->columns(1);
            }
        }

        $fields[] = Section::make('Data Laporan')
            ->description('Silakan lengkapi semua field yang diperlukan untuk laporan harian Anda.')
            ->schema($formFieldsSchema)
            ->columns(1);

        // Compliance Calculation Section
        if ($includeCompliance) {
            $fields[] = Section::make('Compliance Calculation')
                ->schema([
                    Placeholder::make('compliance_calculation')
                        ->content(fn($get) => static::generateComplianceDisplay($formTemplate, $get))
                        ->columnSpanFull(),
                ]);
        }

        return $fields;
    }

    /**
     * Generate compliance display HTML
     */
    public static function generateComplianceDisplay(FormTemplate $formTemplate, callable $get): HtmlString
    {
        try {
            $currentData = [];
            foreach ($formTemplate->formFields as $field) {
                if ($field->field_type === 'time_duration') {
                    $currentData[$field->field_key . '_start_time'] = $get($field->field_key . '_start_time');
                    $currentData[$field->field_key . '_end_time'] = $get($field->field_key . '_end_time');
                    $currentData[$field->field_key . '_valid_indicator'] = $get($field->field_key . '_valid_indicator');
                    $currentData[$field->field_key . '_valid_duration_setting'] = $get($field->field_key . '_valid_duration_setting');
                } elseif ($field->field_type === 'time_range') {
                    $currentData[$field->field_key . '_start_time'] = $get($field->field_key . '_start_time');
                    $currentData[$field->field_key . '_end_time'] = $get($field->field_key . '_end_time');
                    $currentData[$field->field_key . '_input_value'] = $get($field->field_key . '_input_value');
                    $currentData[$field->field_key . '_valid_indicator'] = $get($field->field_key . '_valid_indicator');
                } else {
                    $currentData[$field->field_key] = $get($field->field_key);
                }
            }
        } catch (Exception $e) {
            $currentData = [];
        }

        $component = new ComplianceDisplay($formTemplate, $currentData);

        return new HtmlString(view('components.compliance-display', [
            'formTemplate' => $component->formTemplate,
            'currentData' => $component->currentData,
            'compliance' => $component->compliance,
        ])->render());
    }


    /**
     * Initialize form data with defaults
     */
    public static function initializeFormData(FormTemplate $formTemplate): array
    {
        $data = [];

        foreach ($formTemplate->formFields as $field) {
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
                        $defaultValue = null;
                        break;
                    case 'boolean':
                        // Leave as null for selects to show placeholder
                        $defaultValue = null;
                        break;
                    case 'multi_select':
                        $defaultValue = [];
                        break;
                    case 'time_duration':
                        // Set defaults for composite sub-fields
                        $data[$field->field_key . '_start_time'] = null;
                        $data[$field->field_key . '_end_time'] = null;
                        // $data[$field->field_key . '_valid_duration_setting'] = self::convertMinutesToTime($field->default_valid_duration ?? 480);
                        $data[$field->field_key . '_valid_indicator'] = '0';
                        break;
                }
            }

            $data[$field->field_key] = $defaultValue;
        }

        return $data;
    }
}
