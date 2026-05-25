<?php

namespace App\Observers;

use Exception;
use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImutProfileObserver
{
    /**
     * Handle the ImutProfile "created" event.
     */
    public function created(ImutProfile $imutProfile): void
    {
        // Only create FormTemplate if this is the active profile
        // This prevents creating multiple FormTemplates for overlapping periods
        if ($this->shouldCreateFormTemplate($imutProfile)) {
            $this->createFormTemplate($imutProfile);
            Log::info("✅ ImutProfile created: ID {$imutProfile->id} - FormTemplate auto-created");
        } else {
            Log::info("ℹ️  ImutProfile created: ID {$imutProfile->id} - FormTemplate NOT created (conditions not met)");
        }
    }

    /**
     * Handle the ImutProfile "updated" event.
     */
    public function updated(ImutProfile $imutProfile): void
    {
        // Check if validity dates changed and affect FormTemplate requirement
        if ($imutProfile->wasChanged(['valid_from', 'valid_until'])) {
            Log::info("ImutProfile {$imutProfile->id} validity dates changed, checking FormTemplate requirement");

            // If profile became active and doesn't have FormTemplate, create one
            if ($this->shouldCreateFormTemplate($imutProfile)) {
                $this->createFormTemplate($imutProfile);
                Log::info("✅ ImutProfile updated: ID {$imutProfile->id} - FormTemplate auto-created due to validity change");
            }
        }
    }

    /**
     * Handle the ImutProfile "deleted" event.
     */
    public function deleted(ImutProfile $imutProfile): void
    {
        // Note: FormTemplates will be cascade deleted by database constraints
        // This is just for logging purposes
        Log::info("ImutProfile {$imutProfile->id} deleted - associated FormTemplates will be cascade deleted");
    }

    /**
     * Check if we should create FormTemplate for this profile
     */
    private function shouldCreateFormTemplate(ImutProfile $imutProfile): bool
    {
        // Only create FormTemplate if this profile is currently active
        // (no valid_until date or valid_until is in the future)

        $now = now()->toDateString();

        // Profile must be active now
        if (!$imutProfile->isValidOnDate($now)) {
            Log::info("Profile {$imutProfile->id} not active on {$now}, skipping FormTemplate creation");
            return false;
        }

        // Check if FormTemplate already exists for this profile (active templates only)
        $existingTemplate = $imutProfile->activeFormTemplate;
        if ($existingTemplate) {
            Log::info("Active FormTemplate already exists for profile {$imutProfile->id}, skipping creation");
            return false;
        }

        // Check if there are other active profiles for the same ImutData with existing FormTemplates
        $otherActiveProfilesWithTemplates = ImutProfile::where('imut_data_id', $imutProfile->imut_data_id)
            ->where('id', '!=', $imutProfile->id)
            ->validOnDate($now)
            ->whereHas('formTemplates')
            ->exists();

        // Don't create FormTemplate if there are other active profiles with templates
        if ($otherActiveProfilesWithTemplates) {
            Log::warning("Multiple active profiles with FormTemplates detected for ImutData {$imutProfile->imut_data_id}. FormTemplate creation skipped.");
            return false;
        }

        return true;
    }

    /**
     * Create FormTemplate automatically when ImutProfile is created
     */
    private function createFormTemplate(ImutProfile $imutProfile): void
    {
        try {
            // Load ImutData relation to avoid N+1 queries
            $imutProfile->load('imutData');

            Log::info("Creating FormTemplate for ImutProfile {$imutProfile->id}: {$imutProfile->imutData->title}");

            // First try to find JSON configuration based on ImutData title
            $jsonConfig = $this->findJsonConfigByTitle($imutProfile->imutData->title);

            if ($jsonConfig) {
                $this->createTemplateFromJson($imutProfile, $jsonConfig);
                Log::info("FormTemplate created from JSON for ImutProfile {$imutProfile->id}");
            } else {
                $this->createDefaultYesNoTemplate($imutProfile);
                Log::info("Default FormTemplate created for ImutProfile {$imutProfile->id}");
            }
        } catch (Exception $e) {
            Log::error("Failed to create FormTemplate for ImutProfile {$imutProfile->id}", [
                'error' => $e->getMessage(),
                'imut_data_title' => $imutProfile->imutData->title ?? 'Unknown',
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Search for JSON configuration files that match ImutData title
     */
    private function findJsonConfigByTitle(string $title): ?array
    {
        // Search in form-configurations directory
        $configPath = database_path('data/form-configurations');

        if (!File::exists($configPath)) {
            Log::info("Form configurations directory not found: {$configPath}");
            return null;
        }

        $files = File::files($configPath);
        Log::info("Searching for JSON config matching title: {$title}");

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $content = json_decode(File::get($file->getPathname()), true);

                if (isset($content['form_template']['title'])) {
                    $jsonTitle = $content['form_template']['title'];
                    Log::info("Checking JSON file: {$file->getFilename()} with title: {$jsonTitle}");

                    // Use improved title matching (normalize + stricter threshold)
                    if ($this->titlesMatch($title, $jsonTitle)) {
                        Log::info("Found matching JSON config: {$file->getFilename()} for title: {$title}");
                        return $content;
                    }
                }
            }
        }

        // Also search in seeders data directory
        $seedersPath = database_path('seeders/data');
        if (File::exists($seedersPath)) {
            $files = File::files($seedersPath);

            foreach ($files as $file) {
                if ($file->getExtension() === 'json') {
                    $content = json_decode(File::get($file->getPathname()), true);

                    if (isset($content['form_template']['title'])) {
                        $jsonTitle = $content['form_template']['title'];
                        Log::info("Checking seeders JSON file: {$file->getFilename()} with title: {$jsonTitle}");

                        // Use improved title matching (normalize + stricter threshold)
                        if ($this->titlesMatch($title, $jsonTitle)) {
                            Log::info("Found matching JSON config in seeders: {$file->getFilename()} for title: {$title}");
                            return $content;
                        }
                    }
                }
            }
        }

        Log::info("No matching JSON configuration found for: {$title}");
        return null;
    }

    /**
     * Determine whether two titles are a good match.
     * Prioritize exact normalized match; otherwise require at least
     * 2 matching meaningful words or a match ratio >= 0.5.
     */
    private function titlesMatch(string $title, string $jsonTitle): bool
    {
        $normalize = function (string $s): string {
            $s = mb_strtolower($s);
            // remove punctuation and parentheses, keep alphanumerics and spaces
            $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s);
            $s = preg_replace('/\s+/u', ' ', $s);
            return trim($s);
        };

        $t1 = $normalize($title);
        $t2 = $normalize($jsonTitle);

        if ($t1 === $t2) {
            return true;
        }

        $words1 = array_filter(explode(' ', $t1), function ($w) {
            return mb_strlen($w) > 3;
        });
        $words2 = array_filter(explode(' ', $t2), function ($w) {
            return mb_strlen($w) > 3;
        });

        if (count($words1) === 0 || count($words2) === 0) {
            return false;
        }

        $matchCount = 0;
        foreach ($words1 as $word) {
            foreach ($words2 as $jsonWord) {
                if (strpos($jsonWord, $word) !== false || strpos($word, $jsonWord) !== false) {
                    $matchCount++;
                    break;
                }
            }
        }

        $maxWords = max(count($words1), count($words2));
        $ratio = $matchCount / $maxWords;

        return ($matchCount >= 2) || ($ratio >= 0.5);
    }

    /**
     * Create FormTemplate from JSON configuration
     */
    private function createTemplateFromJson(ImutProfile $imutProfile, array $jsonConfig): void
    {
        $templateData = $jsonConfig['form_template'];

        // Create FormTemplate
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $imutProfile->id,
            'title' => $templateData['title'],
            'description' => $templateData['description'] ?? "Template untuk {$imutProfile->imutData->title}",
            'compliance_method' => $templateData['compliance_method'] ?? 'auto_calculate',
            'auto_fail_on_critical' => $templateData['auto_fail_on_critical'] ?? false,
            'scoring_config' => $templateData['scoring_config'] ?? null,
        ]);

        // Create form fields from JSON
        if (isset($jsonConfig['form_fields'])) {
            foreach ($jsonConfig['form_fields'] as $fieldData) {
                $formField = EnhancedFormField::create([
                    'form_template_id' => $formTemplate->id,
                    'field_key' => $fieldData['field_key'],
                    'field_label' => $fieldData['field_label'],
                    'field_description' => $fieldData['field_description'] ?? null,
                    'field_type' => $fieldData['field_type'],
                    'validation_config' => $fieldData['validation_config'] ?? [],
                    'compliance_weight' => $fieldData['compliance_weight'] ?? 1,
                    'is_critical_field' => $fieldData['is_critical_field'] ?? false,
                    'order_index' => $fieldData['order_index'] ?? 1,
                    'conditional_logic' => $fieldData['conditional_logic'] ?? null,
                    'compliance_rules' => $fieldData['compliance_rules'] ?? null,
                ]);

                // Create options if available
                if (isset($fieldData['options'])) {
                    foreach ($fieldData['options'] as $optionIndex => $optionData) {
                        FormFieldOption::create([
                            'enhanced_form_field_id' => $formField->id,
                            'option_text' => $optionData['option_text'],
                            'option_value' => $optionData['option_value'],
                            'is_correct' => $optionData['is_correct'] ?? false,
                            // Calculate compliance_value: 100 if correct, 0 if not
                            'compliance_value' => ($optionData['is_correct'] ?? false) ? 100 : 0,
                            'order_index' => $optionIndex + 1,
                        ]);
                    }
                }
            }
        }

        Log::info("FormTemplate created from JSON for ImutProfile: {$imutProfile->imutData->title} - Version: {$imutProfile->version}");
    }

    /**
     * Create default Yes/No template when no JSON config is found
     */
    private function createDefaultYesNoTemplate(ImutProfile $imutProfile): void
    {
        // Create FormTemplate
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $imutProfile->id,
            'title' => "Form {$imutProfile->imutData->title} - {$imutProfile->version}",
            'description' => "Template default untuk {$imutProfile->imutData->title} versi {$imutProfile->version} dengan pilihan Ya/Tidak",
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => false,
        ]);

        // Create default Yes/No field
        $formField = EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'compliance_status',
            'field_label' => 'Status Kepatuhan',
            'field_description' => 'Apakah memenuhi standar yang ditetapkan?',
            'field_type' => 'single_select',
            'validation_config' => ['required' => true],
            'compliance_weight' => 10,
            'is_critical_field' => true,
            'order_index' => 1,
        ]);

        // Create Yes/No options
        FormFieldOption::create([
            'enhanced_form_field_id' => $formField->id,
            'option_text' => 'Ya',
            'option_value' => 'ya',
            'is_correct' => true,
            'compliance_value' => 100,
            'order_index' => 1,
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $formField->id,
            'option_text' => 'Tidak',
            'option_value' => 'tidak',
            'is_correct' => false,
            'compliance_value' => 0,
            'order_index' => 2,
        ]);

        Log::info("Default Yes/No FormTemplate created for ImutProfile: {$imutProfile->imutData->title} - Version: {$imutProfile->version}");
    }
}
