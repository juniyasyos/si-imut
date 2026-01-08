<?php

namespace App\Services\DynamicForm;

use App\Filament\Resources\ImutProfileResource\Pages\Helper\FormFields;
use App\Models\FormTemplate;
use Filament\Forms\Components\Section;
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
                $currentData[$field->field_key] = $get($field->field_key);
            }
        } catch (\Exception $e) {
            // Handle validation errors by using empty data
            $currentData = [];
        }

        $compliance = ComplianceCalculatorService::calculateCompliance($formTemplate, $currentData);

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
            $field = $formTemplate->formFields->where('field_key', $fieldKey)->first();
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

            $data[$field->field_key] = $defaultValue;
        }

        return $data;
    }
}
