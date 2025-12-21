<?php

namespace App\Services\FormBuilder;

use App\Models\FormTemplate;
use App\Models\FormHeader;
use App\Models\ImutData;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Str;

class FormDataService
{
    public function loadFormData(ImutData $record): array
    {
        // Try to load from new FormTemplate first, fallback to old FormHeader
        $formTemplate = FormTemplate::where('imut_data_id', $record->id)->first();

        if ($formTemplate) {
            return $this->loadFromFormTemplate($formTemplate);
        } else {
            return $this->loadFromLegacyFormHeader($record);
        }
    }

    private function loadFromFormTemplate(FormTemplate $formTemplate): array
    {
        $fields = $formTemplate->formFields->map(function ($field) {
            $options = $field->options->map(function ($option) {
                return [
                    'option_text' => $option->option_text,
                    'option_value' => $option->option_value,
                    'compliance_value' => $option->compliance_value,
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

    private function loadFromLegacyFormHeader(ImutData $record): array
    {
        $formHeader = FormHeader::where('imutdata_id', $record->id)->first();

        if ($formHeader) {
            $fields = $formHeader->formFields->map(function ($field) {
                $options = $field->options;

                // Konversi options dari array sederhana ke format repeater
                if (is_array($options) && !empty($options)) {
                    $firstItem = reset($options);

                    if (!is_array($firstItem)) {
                        // Konversi dari array sederhana ke format repeater
                        $options = collect($options)->map(function ($item) {
                            return [
                                'label' => $item,
                                'value' => Str::slug($item, '_'),
                            ];
                        })->toArray();
                    }
                }

                return [
                    'id' => $field->id,
                    'field_key' => $field->key,
                    'field_label' => $field->label, // Map legacy label to field_label
                    'field_description' => $field->description,
                    'field_type' => FormFieldMapper::mapLegacyFieldType($field->type),
                    'validation_config' => ['required' => $field->is_required],
                    'compliance_weight' => 1,
                    'is_critical_field' => false,
                    'conditional_logic' => null,
                    'has_conditional_logic' => false, // Legacy fields don't have conditional logic
                    'options' => $options,
                    'order_index' => $field->order,
                ];
            })->toArray();

            return [
                'title' => $formHeader->title,
                'description' => $formHeader->description,
                'compliance_method' => 'auto_calculate',
                'auto_fail_on_critical' => true,
                'fields' => $fields,
            ];
        }

        return [
            'title' => '',
            'description' => '',
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => true,
            'fields' => [],
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
