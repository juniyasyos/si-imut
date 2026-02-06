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
        } catch (\Exception $e) {
            $currentData = [];
        }

        // if (!empty($currentData['hand_hygiene_method']) && !empty($currentData['hand_hygiene_indication']) && !empty($currentData['six_steps_compliance'])) {
        //     dd($currentData);
        // }

        $compliance = ComplianceCalculatorService::calculateCompliance($formTemplate, $currentData);

        $html = '<div class="space-y-4">';

        // Overall Score
        $isCompliant = $compliance['score'] >= 100;
        $scoreColor = $isCompliant ? 'green' : 'red';
        $complianceStatus = $isCompliant ? '✅ PATUH' : '❌ TIDAK PATUH';

        $html .= '<div class="p-4 rounded-lg' . ($isCompliant ? ' bg-green-50 dark:bg-green-900/20 border border-green-200' : ' bg-red-50 dark:bg-red-900/20 border border-red-200') . '">';
        $html .= '<h3 class="text-lg font-semibold mb-2 text-' . $scoreColor . '-800">Overall Compliance Score</h3>';
        $html .= '<div class="text-3xl font-bold text-' . $scoreColor . '-600 mb-2">' . number_format($compliance['score'], 1) . '%</div>';
        $html .= '<div class="text-lg font-semibold text-' . $scoreColor . '-700">' . $complianceStatus . '</div>';
        $html .= '<p class="text-sm text-' . $scoreColor . '-600 mt-2">Pertanyaannya harus dijawab dengan benar 100% untuk dianggap patuh</p>';
        $html .= '</div>';

        // Field Breakdown
        $html .= '<div class="p-4 rounded-lg bg-gray-50 dark:bg-slate-800/80 border border-gray-200 dark:border-gray-700">';
        $html .= '<h4 class="font-semibold mb-3 text-gray-800 dark:text-gray-200">Rincian Field</h4>';
        $html .= '<div class="space-y-2 text-sm">';

        foreach ($compliance['fields'] as $fieldKey => $fieldData) {
            $field = $formTemplate->formFields->where('field_key', $fieldKey)->first();
            if ($field) {
                $weight = $field->compliance_weight;
                $score = $fieldData['score'];
                $maxScore = $weight;
                $percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;

                $fieldCompliant = $percentage >= 100;
                $statusIcon = $fieldCompliant ? '✅' : '❌';
                $statusColor = $fieldCompliant ? 'green' : 'red';

                $html .= '<div class="p-3 rounded border' . ($fieldCompliant ? ' border-green-200 bg-green-50 dark:bg-green-900/20' : ' border-red-200 bg-red-50 dark:bg-red-900/20') . '">';
                $html .= '<div class="flex justify-between items-center mb-1">';
                $html .= '<span class="font-medium text-' . $statusColor . '-700 dark:text-' . $statusColor . '-300">' . $statusIcon . ' ' . $field->field_label . ($field->is_critical_field ? ' ⚠️' : '') . '</span>';
                $html .= '<span class="text-sm font-semibold text-' . $statusColor . '-700">' . number_format($percentage, 1) . '%</span>';
                $html .= '</div>';
                $html .= '<div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">';
                $html .= '<span>Skor: ' . number_format($score, 1) . '/' . $weight . '</span>';
                $html .= '<span class="text-' . $statusColor . '-600">' . ($fieldCompliant ? 'Benar' : 'Ada kesalahan') . '</span>';
                $html .= '</div>';
                $html .= '</div>';
            }
        }

        $html .= '</div></div>';

        // Auto-fail warnings or Success message
        if ($isCompliant) {
            $html .= '<div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200">';
            $html .= '<h4 class="font-semibold text-green-700 dark:text-green-300 mb-2">✅ Compliance Terpenuhi</h4>';
            $html .= '<p class="text-sm text-green-700 dark:text-green-300">Semua pertanyaan dijawab dengan benar</p>';
            $html .= '</div>';
        } else if (!empty($compliance['warnings'])) {
            $html .= '<div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200">';
            $html .= '<h4 class="font-semibold text-red-800 mb-2">❌ Ketidaksesuaian Ditemukan</h4>';
            $html .= '<ul class="text-sm text-red-700 dark:text-red-300 space-y-1">';
            foreach ($compliance['warnings'] as $warning) {
                $html .= '<li class="flex gap-2"><span class="flex-shrink-0">•</span><span>' . $warning . '</span></li>';
            }
            $html .= '</ul>';
            $html .= '<p class="text-xs text-red-600 dark:text-red-400 mt-2 italic">Silakan periksa kembali jawaban Anda untuk mencapai compliance 100%.</p>';
            $html .= '</div>';
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

    private static function convertMinutesToTime(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
