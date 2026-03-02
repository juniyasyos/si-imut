<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\Models\ImutProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FormTemplateVersionService
{
    /**
     * Create a new version of a form template
     */
    public function createNewVersion(FormTemplate $baseTemplate, array $data = []): FormTemplate
    {
        // Retry logic to handle potential race conditions with version generation
        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                return DB::transaction(function () use ($baseTemplate, $data) {
                    // Load current template with relationships
                    $baseTemplate->load('formFields.options');

                    // Create new template
                    $newTemplate = $baseTemplate->replicate();
                    $newTemplate->parent_template_id = $baseTemplate->id;
                    $newTemplate->version = $this->generateNextVersion($baseTemplate->imut_profile_id);
                    $newTemplate->is_active = false;
                    $newTemplate->created_by_user_id = Auth::id();
                    $newTemplate->valid_from = now()->toDateString();
                    $newTemplate->valid_until = null;

                    // Override with provided data
                    foreach ($data as $key => $value) {
                        if ($key !== 'id' && $key !== 'created_at' && $key !== 'updated_at') {
                            $newTemplate->$key = $value;
                        }
                    }

                    $newTemplate->save();

                    // Replicate form fields and their options
                    $this->replicateFormFields($baseTemplate, $newTemplate);

                    return $newTemplate;
                });
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $attempt++;

                // If this is the last attempt, throw the exception
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }

                // Wait a small random amount before retrying to avoid thundering herd
                usleep(random_int(100, 500) * 1000); // 100-500ms
            }
        }
    }

    /**
     * Activate a specific template version
     */
    public function activateVersion(FormTemplate $template): bool
    {
        return DB::transaction(function () use ($template) {
            // Deactivate all other templates for this profile
            FormTemplate::where('imut_profile_id', $template->imut_profile_id)
                ->where('id', '!=', $template->id)
                ->update([
                    'is_active' => false,
                    'valid_until' => now()->toDateString()
                ]);

            // Activate this template
            return $template->update([
                'is_active' => true,
                'valid_from' => now()->toDateString(),
                'valid_until' => null
            ]);
        });
    }

    /**
     * Deactivate a template version
     */
    public function deactivateVersion(FormTemplate $template): bool
    {
        return $template->update([
            'is_active' => false,
            'valid_until' => now()->toDateString()
        ]);
    }

    /**
     * Get all versions for a profile
     */
    public function getVersionHistory(int $profileId): Collection
    {
        return FormTemplate::where('imut_profile_id', $profileId)
            ->with(['createdBy', 'parentTemplate'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active template for a profile
     */
    public function getActiveTemplate(int $profileId): ?FormTemplate
    {
        return FormTemplate::where('imut_profile_id', $profileId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Compare two template versions
     */
    public function compareVersions(FormTemplate $version1, FormTemplate $version2): array
    {
        $version1->load('formFields.options');
        $version2->load('formFields.options');

        $differences = [];

        // Compare basic template properties
        $basicFields = ['title', 'description', 'compliance_method', 'auto_fail_on_critical', 'scoring_config'];
        foreach ($basicFields as $field) {
            if ($version1->$field !== $version2->$field) {
                $differences['template'][$field] = [
                    'v1' => $version1->$field,
                    'v2' => $version2->$field
                ];
            }
        }

        // Compare form fields
        $v1Fields = $version1->formFields->keyBy('field_key');
        $v2Fields = $version2->formFields->keyBy('field_key');

        // Fields added in v2
        $addedFields = $v2Fields->diffKeys($v1Fields);
        if ($addedFields->isNotEmpty()) {
            $differences['fields']['added'] = $addedFields->values()->toArray();
        }

        // Fields removed in v2  
        $removedFields = $v1Fields->diffKeys($v2Fields);
        if ($removedFields->isNotEmpty()) {
            $differences['fields']['removed'] = $removedFields->values()->toArray();
        }

        // Fields modified between versions
        $modifiedFields = [];
        $commonFields = $v1Fields->intersectByKeys($v2Fields);

        foreach ($commonFields as $fieldKey => $v1Field) {
            $v2Field = $v2Fields[$fieldKey];
            $fieldDiff = $this->compareFormFields($v1Field, $v2Field);
            if (!empty($fieldDiff)) {
                $modifiedFields[$fieldKey] = $fieldDiff;
            }
        }

        if (!empty($modifiedFields)) {
            $differences['fields']['modified'] = $modifiedFields;
        }

        return $differences;
    }

    /**
     * Archive old versions (soft delete)
     */
    public function archiveOldVersions(int $profileId, int $keepRecentCount = 5): int
    {
        $versionsToArchive = FormTemplate::where('imut_profile_id', $profileId)
            ->where('is_active', false)
            ->orderBy('created_at', 'desc')
            ->skip($keepRecentCount)
            ->pluck('id');

        if ($versionsToArchive->isEmpty()) {
            return 0;
        }

        return FormTemplate::whereIn('id', $versionsToArchive)->delete();
    }

    /**
     * Restore an archived version
     */
    public function restoreVersion(int $templateId): ?FormTemplate
    {
        $template = FormTemplate::withTrashed()->find($templateId);

        if ($template && $template->trashed()) {
            $template->restore();
            return $template;
        }

        return null;
    }

    /**
     * Get template statistics for a profile
     */
    public function getProfileTemplateStats(int $profileId): array
    {
        $templates = FormTemplate::withTrashed()
            ->where('imut_profile_id', $profileId)
            ->get();

        return [
            'total_versions' => $templates->count(),
            'active_versions' => $templates->where('is_active', true)->count(),
            'archived_versions' => $templates->whereNotNull('deleted_at')->count(),
            'latest_version' => $templates->max('version'),
            'first_created' => $templates->min('created_at'),
            'last_updated' => $templates->max('updated_at')
        ];
    }

    /**
     * Generate next version number
     */
    private function generateNextVersion(int $profileId): string
    {
        $lastTemplate = FormTemplate::where('imut_profile_id', $profileId)
            ->whereRaw('version REGEXP "^v[0-9]+\\.[0-9]+$"') // Only valid version formats
            ->orderByRaw('CAST(SUBSTRING(version, 2, LOCATE(".", version) - 2) AS UNSIGNED) DESC') // Order by major version
            ->orderByRaw('CAST(SUBSTRING(version, LOCATE(".", version) + 1) AS UNSIGNED) DESC') // Then by minor version
            ->first();

        if (!$lastTemplate) {
            return 'v1.0';
        }

        // Extract version number and increment
        if (preg_match('/v(\d+)\.(\d+)/', $lastTemplate->version, $matches)) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2] + 1;

            return "v{$major}.{$minor}";
        }

        // Fallback if version format is unexpected
        return 'v1.0';
    }

    /**
     * Replicate form fields from base template to new template
     */
    private function replicateFormFields(FormTemplate $baseTemplate, FormTemplate $newTemplate): void
    {
        $baseTemplate->formFields->each(function ($field) use ($newTemplate) {
            $newField = $field->replicate();
            $newField->form_template_id = $newTemplate->id;
            $newField->save();

            // Replicate field options if they exist
            if ($field->relationLoaded('options')) {
                $field->options->each(function ($option) use ($newField) {
                    $newOption = $option->replicate();
                    $newOption->enhanced_form_field_id = $newField->id;
                    $newOption->save();
                });
            }
        });
    }

    /**
     * Compare two form fields and return differences
     */
    private function compareFormFields($field1, $field2): array
    {
        $differences = [];
        $compareFields = [
            'field_label',
            'field_description',
            'field_type',
            'validation_config',
            'compliance_weight',
            'is_critical_field',
            'conditional_logic',
            'order_index'
        ];

        foreach ($compareFields as $field) {
            if ($field1->$field !== $field2->$field) {
                $differences[$field] = [
                    'old' => $field1->$field,
                    'new' => $field2->$field
                ];
            }
        }

        // Compare options if they exist
        if ($field1->relationLoaded('options') && $field2->relationLoaded('options')) {
            $optionsDiff = $this->compareFieldOptions($field1->options, $field2->options);
            if (!empty($optionsDiff)) {
                $differences['options'] = $optionsDiff;
            }
        }

        return $differences;
    }

    /**
     * Compare field options between two collections
     */
    private function compareFieldOptions($options1, $options2): array
    {
        $differences = [];

        $options1Map = $options1->keyBy('option_key');
        $options2Map = $options2->keyBy('option_key');

        // Added options
        $added = $options2Map->diffKeys($options1Map);
        if ($added->isNotEmpty()) {
            $differences['added'] = $added->values()->toArray();
        }

        // Removed options
        $removed = $options1Map->diffKeys($options2Map);
        if ($removed->isNotEmpty()) {
            $differences['removed'] = $removed->values()->toArray();
        }

        // Modified options
        $modified = [];
        foreach ($options1Map->intersectByKeys($options2Map) as $key => $option1) {
            $option2 = $options2Map[$key];
            $optionFields = ['option_label', 'compliance_value', 'option_description', 'order_index'];

            foreach ($optionFields as $field) {
                if ($option1->$field !== $option2->$field) {
                    $modified[$key][$field] = [
                        'old' => $option1->$field,
                        'new' => $option2->$field
                    ];
                }
            }
        }

        if (!empty($modified)) {
            $differences['modified'] = $modified;
        }

        return $differences;
    }
}
