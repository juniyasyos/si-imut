<?php

namespace App\Services\FormBuilder;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class FormPersistenceService
{
    public function saveFormData(ImutProfile $record, array $data): void
    {
        // Clean up any duplicate templates first (safety measure)
        $this->cleanupDuplicateTemplates($record);

        // Update existing form template or create new one
        $this->saveToEnhancedFormat($record, $data);
    }

    private function saveToEnhancedFormat(ImutProfile $record, array $data): void
    {
        // Ensure only one template per profile - find or create
        $formTemplate = FormTemplate::where('imut_profile_id', $record->id)->first();

        if ($formTemplate) {
            // Update existing form template
            $formTemplate->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'compliance_method' => $data['compliance_method'],
                'auto_fail_on_critical' => $data['auto_fail_on_critical'],
            ]);

            // Delete existing fields and options, then recreate
            $formTemplate->formFields()->delete();
        } else {
            // Create new form template
            $formTemplate = FormTemplate::create([
                'imut_profile_id' => $record->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'compliance_method' => $data['compliance_method'],
                'auto_fail_on_critical' => $data['auto_fail_on_critical'],
                'status' => true,
            ]);
        }

        $this->saveEnhancedFields($formTemplate, $data['fields'] ?? []);
    }

    private function saveEnhancedFields(FormTemplate $formTemplate, array $fields): void
    {
        foreach ($fields as $index => $fieldData) {
            // Only save conditional_logic if has_conditional_logic is true and conditional_logic has data
            $conditionalLogic = null;
            if (($fieldData['has_conditional_logic'] ?? false) && !empty($fieldData['conditional_logic'])) {
                $conditionalLogic = $fieldData['conditional_logic'];
            }

            $field = EnhancedFormField::create([
                'form_template_id' => $formTemplate->id,
                'field_key' => $this->generateFieldKey($fieldData),
                'field_label' => $fieldData['field_label'],
                'field_description' => $fieldData['field_description'] ?? null,
                'field_type' => $fieldData['field_type'],
                'validation_config' => $fieldData['validation_config'] ?? [],
                'history_suggestions' => $this->processHistorySuggestions($fieldData['history_suggestions'] ?? []),
                'compliance_weight' => $fieldData['compliance_weight'] ?? 2,
                'is_critical_field' => $fieldData['is_critical_field'] ?? false,
                'conditional_logic' => $conditionalLogic,
                'compliance_rules' => $fieldData['compliance_rules'] ?? null,
                'order_index' => $index + 1,
                'time_format' => $fieldData['time_format'] ?? 'HM',
                'default_valid_duration' => $fieldData['default_valid_duration'] ?? 480,
                'status' => true,
            ]);

            $this->saveFieldOptions($field, $fieldData['options'] ?? []);
        }
    }

    private function processHistorySuggestions(array $suggestions): ?array
    {
        if (empty($suggestions)) {
            return null;
        }

        // Extract values from repeater structure and filter out empty ones
        $processed = array_filter(array_map(function ($item) {
            return trim($item['value'] ?? '');
        }, $suggestions));

        // Remove duplicates and re-index
        $processed = array_unique($processed);
        $processed = array_values($processed);

        // Limit to 10 suggestions
        return array_slice($processed, 0, 10);
    }

    private function saveFieldOptions(EnhancedFormField $field, array $options): void
    {
        foreach ($options as $index => $optionData) {
            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => $optionData['label'] ?? $optionData['option_text'] ?? '',
                'option_value' => $optionData['value'] ?? $optionData['option_value'] ?? '',
                'is_correct' => $optionData['is_correct'] ?? true,
                'order_index' => $index + 1,
                'status' => true,
            ]);
        }
    }

    private function generateFieldKey(array $fieldData): string
    {
        if (!empty($fieldData['field_key'])) {
            return $fieldData['field_key'];
        }

        return Str::slug($fieldData['field_label'] ?? 'field', '_');
    }

    private function mapToLegacyFieldType(string $enhancedType): string
    {
        $mapping = [
            'text_input' => 'text',
            'numeric_input' => 'number',
            'email_input' => 'email',
            'url_input' => 'url',
            'select_dropdown' => 'select',
            'radio_options' => 'radio',
            'checkbox_options' => 'checkbox',
            'textarea_input' => 'textarea',
            'date_picker' => 'date',
            'datetime_picker' => 'date',
            'time_picker' => 'date',
            'password_input' => 'text',
            'phone_input' => 'text',
            'file_upload' => 'file',
            'image_upload' => 'file',
        ];

        return $mapping[$enhancedType] ?? 'text';
    }

    private function formatLegacyOptions(array $options): array
    {
        if (empty($options)) {
            return [];
        }

        $formatted = [];
        foreach ($options as $option) {
            if (is_array($option)) {
                $formatted[] = [
                    'label' => $option['label'] ?? $option['option_text'] ?? '',
                    'value' => $option['value'] ?? $option['option_value'] ?? '',
                ];
            }
        }

        return $formatted;
    }

    public function hasExistingResponses(ImutProfile $record): bool
    {
        $existingTemplate = FormTemplate::where('imut_profile_id', $record->id)->first();
        return $existingTemplate && $existingTemplate->dailyReportResponses()->exists();
    }

    public function getResponseCount(ImutProfile $record): int
    {
        $existingTemplate = FormTemplate::where('imut_profile_id', $record->id)->first();
        return $existingTemplate ? $existingTemplate->dailyReportResponses()->count() : 0;
    }

    public function calculateAndUpdateCompliance(ImutProfile $record): void
    {
        $formTemplate = FormTemplate::where('imut_profile_id', $record->id)->first();

        if ($formTemplate) {
            // Calculate compliance based on form structure (without actual responses)
            // This gives a baseline compliance score for the form template itself
            $complianceScore = $formTemplate->calculateCompliance([]);

            // Update record dengan compliance score
            $record->update([
                'compliance_score' => $complianceScore['total_score'] ?? 0,
                'is_compliant' => $complianceScore['compliance_status'] ?? false,
            ]);
        }
    }

    private function cleanupDuplicateTemplates(ImutProfile $record): void
    {
        // Find all templates for this profile
        $templates = FormTemplate::where('imut_profile_id', $record->id)
            ->orderBy('created_at')
            ->get();

        if ($templates->count() <= 1) {
            return; // No duplicates
        }

        // Keep the latest template, delete others
        $latestTemplate = $templates->last();
        $templatesToDelete = $templates->where('id', '!=', $latestTemplate->id);

        foreach ($templatesToDelete as $template) {
            // Migrate any responses to the latest template
            DB::table('daily_report_responses')
                ->where('form_template_id', $template->id)
                ->update(['form_template_id' => $latestTemplate->id]);

            $template->delete();
        }
    }
}
