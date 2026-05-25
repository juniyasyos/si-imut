<?php

namespace App\Services\FormBuilder;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Repositories\Interfaces\FormPersistenceRepositoryInterface;

class FormPersistenceService
{
    public function __construct(
        protected FormPersistenceRepositoryInterface $repository
    ) {
    }

    public function saveFormData(ImutProfile $record, array $data): void
    {
        // With versioning system, we don't cleanup duplicate templates
        // Each version should be preserved for audit trail

        // Update existing form template or create new one
        $this->saveToEnhancedFormat($record, $data);
    }

    /**
     * Save form data to a specific template without activating it
     */
    public function saveFormDataToTemplate(FormTemplate $formTemplate, array $data): void
    {
        $this->validateTemplateDateWindow($formTemplate, $data);
        $hasExistingResponses = $this->hasExistingResponsesForTemplate($formTemplate);

        // Prepare compliance settings
        $complianceMethod = $data['compliance_method'] ?? 'auto_calculate';
        $autoFailOnCritical = $data['auto_fail_on_critical'] ?? false;

        // If the payload doesn't include the values, keep existing template values
        $complianceMethod = $data['compliance_method'] ?? $formTemplate->compliance_method ?? $complianceMethod;
        $autoFailOnCritical = $data['auto_fail_on_critical'] ?? $formTemplate->auto_fail_on_critical ?? $autoFailOnCritical;
        $validFrom = $data['valid_from'] ?? ($formTemplate->valid_from ? Carbon::parse($formTemplate->valid_from)->toDateString() : null);
        $validUntil = array_key_exists('valid_until', $data)
            ? $data['valid_until']
            : ($formTemplate->valid_until ? Carbon::parse($formTemplate->valid_until)->toDateString() : null);

        // Update template fields only (don't change is_active status)
        $formTemplate->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'valid_from' => $validFrom,
            'valid_until' => blank($validUntil) ? null : $validUntil,
            'compliance_method' => $complianceMethod,
            'auto_fail_on_critical' => $autoFailOnCritical,
        ]);

        // Reconcile fields with incoming payload in a non-destructive way
        $this->reconcileEnhancedFields($formTemplate, $data['fields'] ?? [], $hasExistingResponses);
    }

    private function validateTemplateDateWindow(FormTemplate $formTemplate, array $data): void
    {
        $profile = $formTemplate->imutProfile;

        if (! $profile) {
            return;
        }

        $validFrom = $data['valid_from'] ?? ($formTemplate->valid_from ? Carbon::parse($formTemplate->valid_from)->toDateString() : null);
        $validUntil = array_key_exists('valid_until', $data)
            ? $data['valid_until']
            : ($formTemplate->valid_until ? Carbon::parse($formTemplate->valid_until)->toDateString() : null);

        if (blank($validFrom)) {
            throw new \InvalidArgumentException('Tanggal berlaku mulai wajib diisi.');
        }

        $profileValidFrom = $profile->valid_from ? Carbon::parse($profile->valid_from)->startOfDay() : null;
        $profileValidUntil = $profile->valid_until ? Carbon::parse($profile->valid_until)->endOfDay() : null;
        $selectedValidFrom = Carbon::parse($validFrom)->startOfDay();
        $selectedValidUntil = blank($validUntil) ? null : Carbon::parse($validUntil)->endOfDay();

        if ($selectedValidUntil && $selectedValidUntil->lt($selectedValidFrom)) {
            throw new \InvalidArgumentException('Berlaku sampai tidak boleh lebih kecil dari berlaku mulai.');
        }

        if ($profileValidFrom && $selectedValidFrom->lt($profileValidFrom)) {
            throw new \InvalidArgumentException('Berlaku mulai tidak boleh kurang dari tanggal valid profile terkait.');
        }

        if ($profileValidUntil && $selectedValidFrom->gt($profileValidUntil)) {
            throw new \InvalidArgumentException('Berlaku mulai tidak boleh melebihi tanggal valid profile terkait.');
        }

        if ($profileValidUntil && $selectedValidUntil && $selectedValidUntil->gt($profileValidUntil)) {
            throw new \InvalidArgumentException('Berlaku sampai tidak boleh melebihi tanggal valid profile terkait.');
        }
    }

    private function saveToEnhancedFormat(ImutProfile $record, array $data): void
    {
        // With versioning: find active template or create new one
        $formTemplate = $record->activeFormTemplate;

        // If there is no active template (e.g. legacy data), fallback to the most recent template for the profile.
        // This prevents duplicate version errors when the template exists but isn't marked as active.
        if (!$formTemplate) {
            $formTemplate = $this->repository->findLatestTemplateForProfile($record);
        }

        // Fail-safe (avoid undefined array key warnings when the form payload doesn't include these keys)
        $complianceMethod = $data['compliance_method'] ?? 'auto_calculate';
        $autoFailOnCritical = $data['auto_fail_on_critical'] ?? false;

        if ($formTemplate) {
            // If the payload doesn't include the values, keep existing template values
            $complianceMethod = $data['compliance_method'] ?? $formTemplate->compliance_method ?? $complianceMethod;
            $autoFailOnCritical = $data['auto_fail_on_critical'] ?? $formTemplate->auto_fail_on_critical ?? $autoFailOnCritical;

            // Update existing active form template (or promote latest template to active)
            $this->repository->updateTemplate($formTemplate, [
                'title' => $data['title'],
                'description' => $data['description'],
                'compliance_method' => $complianceMethod,
                'auto_fail_on_critical' => $autoFailOnCritical,
                'is_active' => true,
            ]);
        } else {
            // Create new active template (first version)
            try {
                $formTemplate = $this->repository->createActiveTemplate($record, [
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

                    $this->repository->updateTemplate($formTemplate, [
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
        $this->reconcileEnhancedFields(
            $formTemplate,
            $data['fields'] ?? [],
            $this->hasExistingResponsesForTemplate($formTemplate)
        );
    }

    /**
     * Reconcile incoming fields with existing fields in a non-destructive way.
     * - update existing fields (matched by field_key)
     * - create new fields
     * - delete only fields that are removed and have NO field_responses
     * - preserve any field that already has responses
     */
    private function reconcileEnhancedFields(FormTemplate $formTemplate, array $fields, bool $preserveHistoricalOptions = true): void
    {
        $existingFields = $this->repository->getFields($formTemplate);
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
                $this->repository->updateField($field, [
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
                $this->reconcileFieldOptions($field, $fieldData['options'] ?? [], $preserveHistoricalOptions);
            } else {
                // Create new field
                $field = $this->repository->createField($formTemplate, [
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
            $this->repository->deleteField($removedField);
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
            $this->repository->createOption($field, [
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
    private function reconcileFieldOptions(EnhancedFormField $field, array $options, bool $preserveHistoricalOptions = true): void
    {
        $existingOptions = $this->repository->getOptions($field);
        $incomingOptionValues = [];

        foreach ($options as $index => $optionData) {
            $optionValue = $optionData['value'] ?? $optionData['option_value'] ?? null;
            $optionText = $optionData['label'] ?? $optionData['option_text'] ?? $optionValue;

            if ($optionValue) {
                $incomingOptionValues[] = $optionValue;
            }

            if ($optionValue && $existingOptions->has($optionValue)) {
                $opt = $existingOptions->get($optionValue);
                $this->repository->updateOption($opt, [
                    'option_text' => $optionText ?? $opt->option_text,
                    'is_correct' => $optionData['is_correct'] ?? $opt->is_correct,
                    'compliance_value' => $optionData['compliance_value'] ?? $opt->compliance_value,
                    'order_index' => $index + 1,
                ]);
            } else {
                // Create new option (safe)
                $this->repository->createOption($field, [
                    'option_text' => $optionText ?? '',
                    'option_value' => $optionValue ?? ($optionText ?? ''),
                    'is_correct' => $optionData['is_correct'] ?? true,
                    'compliance_value' => $optionData['compliance_value'] ?? (($optionData['is_correct'] ?? false) ? 100 : 0),
                    'order_index' => $index + 1,
                ]);
            }
        }

        if ($preserveHistoricalOptions) {
            return;
        }

        $optionsToDelete = $existingOptions->keys()->diff($incomingOptionValues);

        if ($optionsToDelete->isNotEmpty()) {
            $this->repository->deleteOptionsByValues($field, $optionsToDelete->all());
        }
    }

    public function hasExistingResponses(ImutProfile $record): bool
    {
        $template = $this->resolveTemplateForResponseChecks($record);

        if (! $template) {
            return false;
        }

        return $this->repository->hasResponsesForTemplate($template);
    }

    public function getResponseCount(ImutProfile $record): int
    {
        $template = $this->resolveTemplateForResponseChecks($record);

        if (! $template) {
            return 0;
        }

        return $this->repository->countResponsesForTemplate($template);
    }

    /**
     * Check if a specific template has existing responses
     */
    public function hasExistingResponsesForTemplate(FormTemplate $template): bool
    {
        if (! $template) {
            return false;
        }

        return $this->repository->hasResponsesForTemplate($template);
    }

    /**
     * Get response count for a specific template
     */
    public function getResponseCountForTemplate(FormTemplate $template): int
    {
        if (! $template) {
            return 0;
        }

        return $this->repository->countResponsesForTemplate($template);
    }

    public function calculateAndUpdateCompliance(ImutProfile $record): void
    {
        $formTemplate = $record->activeFormTemplate()->first()
            ?? $record->latestFormTemplate()->first();

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
     * Resolve template context for response locking/counting.
     * Prefer active template, fallback to latest when no active exists.
     */
    private function resolveTemplateForResponseChecks(ImutProfile $record): ?FormTemplate
    {
        return $record->activeFormTemplate()->first()
            ?? $record->latestFormTemplate()->first();
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
            $this->repository->deleteResponsesForTemplate($formTemplate);
        }
    }
}
