<?php

namespace App\Services\FormBuilder;

use App\Models\ImutData;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class FormPersistenceService
{
    public function saveFormData(ImutData $record, array $data): void
    {
        // Keep cleanup of legacy data, but persist only the enhanced format (FormTemplate)
        $this->cleanupOldData($record);
        $this->saveToEnhancedFormat($record, $data);
    }

    private function cleanupOldData(ImutData $record): void
    {
        // Hapus data lama jika ada
        FormTemplate::where('imut_data_id', $record->id)->delete();
    }

    private function saveToEnhancedFormat(ImutData $record, array $data): void
    {
        $formTemplate = FormTemplate::create([
            'imut_data_id' => $record->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'compliance_method' => $data['compliance_method'],
            'auto_fail_on_critical' => $data['auto_fail_on_critical'],
            'status' => true,
        ]);

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
                'compliance_weight' => $fieldData['compliance_weight'] ?? 2,
                'is_critical_field' => $fieldData['is_critical_field'] ?? false,
                'conditional_logic' => $conditionalLogic,
                'compliance_rules' => $fieldData['compliance_rules'] ?? null,
                'order_index' => $index + 1,
                'status' => true,
            ]);

            $this->saveFieldOptions($field, $fieldData['options'] ?? []);
        }
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

    public function calculateAndUpdateCompliance(ImutData $record): void
    {
        $formTemplate = FormTemplate::where('imut_data_id', $record->id)->first();

        if ($formTemplate) {
            $complianceScore = $formTemplate->calculateCompliance([]);

            // Update record dengan compliance score
            $record->update([
                'compliance_score' => $complianceScore,
                'is_compliant' => $complianceScore >= 0.8, // 80% threshold
            ]);
        }
    }
}
