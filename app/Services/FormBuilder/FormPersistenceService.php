<?php

namespace App\Services\FormBuilder;

use App\Models\ImutData;
use App\Models\FormTemplate;
use App\Models\FormHeader;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class FormPersistenceService
{
    public function saveFormData(ImutData $record, array $data): void
    {
        $this->cleanupOldData($record);
        $this->saveToEnhancedFormat($record, $data);
        $this->saveLegacyFormat($record, $data);
    }

    private function cleanupOldData(ImutData $record): void
    {
        // Hapus data lama jika ada
        FormTemplate::where('imut_data_id', $record->id)->delete();
        FormHeader::where('imutdata_id', $record->id)->delete();
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
            $field = EnhancedFormField::create([
                'form_template_id' => $formTemplate->id,
                'field_key' => $this->generateFieldKey($fieldData),
                'field_name' => $fieldData['field_name'],
                'field_description' => $fieldData['field_description'] ?? null,
                'field_type' => $fieldData['field_type'],
                'validation_config' => $fieldData['validation_config'] ?? [],
                'compliance_weight' => $fieldData['compliance_weight'] ?? 2,
                'is_critical_field' => $fieldData['is_critical_field'] ?? false,
                'conditional_logic' => $fieldData['conditional_logic'] ?? null,
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
                'option_text' => $optionData['option_text'],
                'option_value' => $optionData['option_value'],
                'compliance_value' => $optionData['compliance_value'] ?? 1,
                'order_index' => $index + 1,
                'status' => true,
            ]);
        }
    }

    private function saveLegacyFormat(ImutData $record, array $data): void
    {
        $formHeader = FormHeader::create([
            'imutdata_id' => $record->id,
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        $this->saveLegacyFields($formHeader, $data['fields'] ?? []);
    }

    private function saveLegacyFields($formHeader, array $fields): void
    {
        foreach ($fields as $index => $fieldData) {
            $legacyType = $this->mapToLegacyFieldType($fieldData['field_type']);
            $options = $this->formatLegacyOptions($fieldData['options'] ?? []);

            $formHeader->formFields()->create([
                'key' => $this->generateFieldKey($fieldData),
                'label' => $fieldData['field_name'],
                'description' => $fieldData['field_description'] ?? '',
                'type' => $legacyType,
                'is_required' => $fieldData['validation_config']['required'] ?? false,
                'options' => $options,
                'order' => $index + 1,
            ]);
        }
    }

    private function generateFieldKey(array $fieldData): string
    {
        if (!empty($fieldData['field_key'])) {
            return $fieldData['field_key'];
        }

        return Str::slug($fieldData['field_name'], '_');
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
            if (is_array($option) && isset($option['option_text'])) {
                $formatted[] = [
                    'label' => $option['option_text'],
                    'value' => $option['option_value'],
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
