<?php

namespace App\Services\FormBuilder;

use App\Models\FormTemplate;
use App\Models\ImutProfile;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Str;

class FormDataService
{
    public function loadFormData(ImutProfile $record): array
    {
        $formTemplate = FormTemplate::where('imut_profile_id', $record->id)->first();

        if (!$formTemplate) {
            return []; // Return empty array if no form template found
        }

        return $this->loadFromFormTemplate($formTemplate);
    }

    private function loadFromFormTemplate(FormTemplate $formTemplate): array
    {
        $fields = $formTemplate->formFields->map(function ($field) {
            $options = $field->options->map(function ($option) {
                return [
                    'label' => $option->option_text, // Map option_text to label for UI
                    'value' => $option->option_value,
                    'is_correct' => $option->is_correct ?? true, // Boolean compliance
                ];
            })->toArray();

            return [
                'id' => $field->id,
                'field_key' => $field->field_key,
                'field_label' => $field->field_label, // Fixed: EnhancedFormField uses field_label column
                'field_description' => $field->field_description,
                'field_type' => $field->field_type ?: 'text', // Fallback for empty field_type
                'validation_config' => $field->validation_config,
                'compliance_weight' => $field->compliance_weight,
                'is_critical_field' => $field->is_critical_field,
                'conditional_logic' => $field->conditional_logic,
                'compliance_rules' => $field->compliance_rules, // Add compliance_rules
                'has_conditional_logic' => !empty($field->conditional_logic), // Add toggle state based on data
                'options' => $options,
                'order_index' => $field->order_index,
            ];
        })->toArray();

        return [
            'title' => $formTemplate->title,
            'description' => $formTemplate->description,
            'compliance_method' => $formTemplate->compliance_method,
            'auto_fail_on_critical' => $formTemplate->auto_fail_on_critical,
            'fields' => $fields,
        ];
    }

    public function getAvailableFields(array $formData): array
    {
        $fields = $formData['fields'] ?? [];

        $options = [];
        foreach ($fields as $field) {
            if (!empty($field['field_label'])) {
                $options[$field['field_key'] ?? Str::slug($field['field_label'])] = $field['field_label'];
            }
        }

        return $options;
    }

    public function getFieldOptions(array $formData, ?string $fieldKey): array
    {
        if (!$fieldKey) return [];

        $fields = $formData['fields'] ?? [];

        foreach ($fields as $field) {
            if (($field['field_key'] ?? Str::slug($field['field_label'])) === $fieldKey) {
                $options = $field['options'] ?? [];
                $result = [];

                foreach ($options as $option) {
                    if (is_array($option) && isset($option['option_text'])) {
                        $result[$option['option_value']] = $option['option_text'];
                    }
                }

                return $result;
            }
        }

        return [];
    }
}
