<?php

namespace App\Services\FormBuilder;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class FormPersistenceService
{
    public function saveFormData(ImutProfile $record, array $data): void
    {
        // With versioning system, we don't cleanup duplicate templates
        // Each version should be preserved for audit trail

        // Update existing form template or create new one
        $this->saveToEnhancedFormat($record, $data);
    }

    private function saveToEnhancedFormat(ImutProfile $record, array $data): void
    {
        // With versioning: find active template or create new one
        $formTemplate = $record->activeFormTemplate;

        // If there is no active template (e.g. legacy data), fallback to the most recent template for the profile.
        // This prevents duplicate version errors when the template exists but isn't marked as active.
        if (!$formTemplate) {
            $formTemplate = FormTemplate::where('imut_profile_id', $record->id)
                ->orderByDesc('created_at')
                ->first();
        }

        // Fail-safe (avoid undefined array key warnings when the form payload doesn't include these keys)
        $complianceMethod = $data['compliance_method'] ?? 'auto_calculate';
        $autoFailOnCritical = $data['auto_fail_on_critical'] ?? false;

        if ($formTemplate) {
            // If the payload doesn't include the values, keep existing template values
            $complianceMethod = $data['compliance_method'] ?? $formTemplate->compliance_method ?? $complianceMethod;
            $autoFailOnCritical = $data['auto_fail_on_critical'] ?? $formTemplate->auto_fail_on_critical ?? $autoFailOnCritical;

            // Update existing active form template (or promote latest template to active)
            $formTemplate->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'compliance_method' => $complianceMethod,
                'auto_fail_on_critical' => $autoFailOnCritical,
                'is_active' => true,
            ]);
        } else {
            // Create new active template (first version)
            try {
                $formTemplate = FormTemplate::create([
                    'imut_profile_id' => $record->id,
                    'version' => 'v1.0', // First version
                    'is_active' => true, // Mark as active
                    'valid_from' => now()->toDateString(),
                    'created_by_user_id' => auth()->id(),
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'compliance_method' => $complianceMethod,
                    'auto_fail_on_critical' => $autoFailOnCritical,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle potential race condition - fetch active template
                if (str_contains($e->getMessage(), 'unique_profile_version') || str_contains($e->getMessage(), 'Duplicate entry')) {
                    // Another active template might exist; fetch and update it instead
                    $formTemplate = $record->activeFormTemplate;

                    if (!$formTemplate) {
                        // Unexpected — rethrow to surface the original problem
                        throw $e;
                    }

                    $complianceMethod = $data['compliance_method'] ?? $formTemplate->compliance_method ?? $complianceMethod;
                    $autoFailOnCritical = $data['auto_fail_on_critical'] ?? $formTemplate->auto_fail_on_critical ?? $autoFailOnCritical;

                    $formTemplate->update([
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'compliance_method' => $complianceMethod,
                        'auto_fail_on_critical' => $autoFailOnCritical,
                        'is_active' => true,
                    ]);
                } else {
                    throw $e;
                }
            }
        }

        // Reconcile fields with incoming payload in a non-destructive way
        $this->reconcileEnhancedFields($formTemplate, $data['fields'] ?? []);
    }

    /**
     * Reconcile incoming fields with existing fields in a non-destructive way.
     * - update existing fields (matched by field_key)
     * - create new fields
     * - delete only fields that are removed and have NO field_responses
     * - preserve any field that already has responses
     */
    private function reconcileEnhancedFields(FormTemplate $formTemplate, array $fields): void
    {
        $existingFields = $formTemplate->formFields()->get()->keyBy('field_key');
        $incomingKeys = [];

        foreach ($fields as $index => $fieldData) {
            $fieldKey = $this->generateFieldKey($fieldData);
            $incomingKeys[] = $fieldKey;

            // Prepare conditional logic only when provided
            $conditionalLogic = null;
            if (($fieldData['has_conditional_logic'] ?? false) && !empty($fieldData['conditional_logic'])) {
                $conditionalLogic = $fieldData['conditional_logic'];
            }

            if ($existingFields->has($fieldKey)) {
                // Update existing field (non-destructive)
                $field = $existingFields->get($fieldKey);
                $field->update([
                    'field_label' => $fieldData['field_label'] ?? $field->field_label,
                    'field_description' => $fieldData['field_description'] ?? $field->field_description,
                    'field_type' => $fieldData['field_type'] ?? $field->field_type,
                    'validation_config' => $fieldData['validation_config'] ?? $field->validation_config,
                    'history_suggestions' => $this->processHistorySuggestions($fieldData['history_suggestions'] ?? []),
                    'compliance_weight' => $fieldData['compliance_weight'] ?? $field->compliance_weight,
                    'is_critical_field' => $fieldData['is_critical_field'] ?? $field->is_critical_field,
                    'conditional_logic' => $conditionalLogic ?? $field->conditional_logic,
                    'compliance_rules' => $fieldData['compliance_rules'] ?? $field->compliance_rules,
                    'order_index' => $index + 1,
                    'time_format' => $fieldData['time_format'] ?? $field->time_format,
                    'default_valid_duration' => $fieldData['default_valid_duration'] ?? $field->default_valid_duration,
                ]);

                // Reconcile options non-destructively (don't remove existing options that may be referenced by historical responses)
                $this->reconcileFieldOptions($field, $fieldData['options'] ?? []);
            } else {
                // Create new field
                $field = EnhancedFormField::create([
                    'form_template_id' => $formTemplate->id,
                    'field_key' => $fieldKey,
                    'field_label' => $fieldData['field_label'] ?? ($fieldData['field_key'] ?? 'field'),
                    'field_description' => $fieldData['field_description'] ?? null,
                    'field_type' => $fieldData['field_type'] ?? 'text',
                    'validation_config' => $fieldData['validation_config'] ?? [],
                    'history_suggestions' => $this->processHistorySuggestions($fieldData['history_suggestions'] ?? []),
                    'compliance_weight' => $fieldData['compliance_weight'] ?? 2,
                    'is_critical_field' => $fieldData['is_critical_field'] ?? false,
                    'conditional_logic' => $conditionalLogic,
                    'compliance_rules' => $fieldData['compliance_rules'] ?? null,
                    'order_index' => $index + 1,
                    'time_format' => $fieldData['time_format'] ?? 'HM',
                    'default_valid_duration' => $fieldData['default_valid_duration'] ?? 480,
                ]);

                $this->saveFieldOptions($field, $fieldData['options'] ?? []);
            }
        }

        // Handle fields that were removed in incoming payload
        $removedKeys = $existingFields->keys()->diff($incomingKeys);
        foreach ($removedKeys as $removedKey) {
            $removedField = $existingFields->get($removedKey);

            // If there are historical field responses, do NOT delete — keep to preserve data
            if ($removedField->fieldResponses()->exists()) {
                // keep existing field intact (non-destructive)
                continue;
            }

            // Safe to delete (no historical responses)
            $removedField->delete();
        }
    }

    private function processHistorySuggestions(array $suggestions): ?array
    {
        if (empty($suggestions)) {
            return null;
        }

        $processed = array_filter(array_map(function ($item) {
            $value = $item['value'] ?? null;

            if (is_array($value)) {
                return null;
            }

            return trim((string) $value);
        }, $suggestions));

        $processed = array_unique($processed);
        $processed = array_values($processed);

        return $processed;
    }

    private function saveFieldOptions(EnhancedFormField $field, array $options): void
    {
        foreach ($options as $index => $optionData) {
            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => $optionData['label'] ?? $optionData['option_text'] ?? '',
                'option_value' => $optionData['value'] ?? $optionData['option_value'] ?? '',
                'is_correct' => $optionData['is_correct'] ?? true,
                'compliance_value' => $optionData['compliance_value'] ?? (($optionData['is_correct'] ?? false) ? 100 : 0),
                'order_index' => $index + 1,
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

    /**
     * Reconcile options for an existing field without deleting historically referenced options.
     * - update matching options by option_value
     * - create new options from incoming payload
     * - DO NOT delete existing options (preserve historical references)
     */
    private function reconcileFieldOptions(EnhancedFormField $field, array $options): void
    {
        $existingOptions = $field->options()->get()->keyBy('option_value');

        foreach ($options as $index => $optionData) {
            $optionValue = $optionData['value'] ?? $optionData['option_value'] ?? null;
            $optionText = $optionData['label'] ?? $optionData['option_text'] ?? $optionValue;

            if ($optionValue && $existingOptions->has($optionValue)) {
                $opt = $existingOptions->get($optionValue);
                $opt->update([
                    'option_text' => $optionText ?? $opt->option_text,
                    'is_correct' => $optionData['is_correct'] ?? $opt->is_correct,
                    'compliance_value' => $optionData['compliance_value'] ?? $opt->compliance_value,
                    'order_index' => $index + 1,
                ]);
            } else {
                // Create new option (safe)
                FormFieldOption::create([
                    'enhanced_form_field_id' => $field->id,
                    'option_text' => $optionText ?? '',
                    'option_value' => $optionValue ?? ($optionText ?? ''),
                    'is_correct' => $optionData['is_correct'] ?? true,
                    'compliance_value' => $optionData['compliance_value'] ?? (($optionData['is_correct'] ?? false) ? 100 : 0),
                    'order_index' => $index + 1,
                ]);
            }
        }

        // Intentionally do not remove existing options to avoid breaking historical responses.
    }

    public function hasExistingResponses(ImutProfile $record): bool
    {
        $existingTemplate = $record->activeFormTemplate;
        return $existingTemplate && $existingTemplate->dailyReportResponses()->exists();
    }

    public function getResponseCount(ImutProfile $record): int
    {
        $existingTemplate = $record->activeFormTemplate;
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

    /**
     * DEPRECATED: Cleanup method not used in versioning system
     * Templates are preserved for audit trail and parent relationships
     */
    private function cleanupDuplicateTemplates(ImutProfile $record): void
    {
        // With versioning system, we preserve all template versions
        // This method is now disabled to prevent foreign key constraint violations
        return;

        // Old logic (commented out for safety):
        // Find all templates for this profile
        /*
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
            // Check for parent relationships before deletion
            $hasChildTemplates = FormTemplate::where('parent_template_id', $template->id)->exists();
            
            if ($hasChildTemplates) {
                continue; // Skip deletion if this template is a parent
            }
            
            // Migrate any responses to the latest template
            DB::table('daily_report_responses')
                ->where('form_template_id', $template->id)
                ->update(['form_template_id' => $latestTemplate->id]);

            $template->delete();
        }
        */
    }

    public function deleteResponses(ImutProfile $record): void
    {
        $formTemplate = $record->activeFormTemplate;
        if ($formTemplate) {
            $formTemplate->dailyReportResponses()->delete();
        }
    }
}
