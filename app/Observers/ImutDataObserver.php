<?php

namespace App\Observers;

use App\Models\ImutData;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImutDataObserver
{
    /**
     * Handle the ImutData "created" event.
     */
    public function created(ImutData $imutData): void
    {
        $this->createFormTemplate($imutData);
        Log::info("✅ ImutData created: ID {$imutData->id} - FormTemplate auto-created");
    }

    /**
     * Create FormTemplate automatically when ImutData is created
     */
    private function createFormTemplate(ImutData $imutData): void
    {
        // First try to find JSON configuration based on ImutData title
        $jsonConfig = $this->findJsonConfigByTitle($imutData->title);

        if ($jsonConfig) {
            $this->createTemplateFromJson($imutData, $jsonConfig);
        } else {
            $this->createDefaultYesNoTemplate($imutData);
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

                    // Check if title matches using keyword search
                    $titleWords = explode(' ', strtolower($title));
                    $jsonTitleWords = explode(' ', strtolower($jsonTitle));

                    // Count matching words
                    $matchCount = 0;
                    foreach ($titleWords as $word) {
                        if (strlen($word) > 3) { // Only consider words longer than 3 chars
                            foreach ($jsonTitleWords as $jsonWord) {
                                if (strpos($jsonWord, $word) !== false || strpos($word, $jsonWord) !== false) {
                                    $matchCount++;
                                    break;
                                }
                            }
                        }
                    }

                    // If we have at least 1 meaningful word match, use this config
                    if ($matchCount > 0) {
                        Log::info("Found matching JSON config: {$file->getFilename()} with {$matchCount} matching words");
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

                        // Check if title matches using keyword search
                        $titleWords = explode(' ', strtolower($title));
                        $jsonTitleWords = explode(' ', strtolower($jsonTitle));

                        // Count matching words
                        $matchCount = 0;
                        foreach ($titleWords as $word) {
                            if (strlen($word) > 3) { // Only consider words longer than 3 chars
                                foreach ($jsonTitleWords as $jsonWord) {
                                    if (strpos($jsonWord, $word) !== false || strpos($word, $jsonWord) !== false) {
                                        $matchCount++;
                                        break;
                                    }
                                }
                            }
                        }

                        // If we have at least 1 meaningful word match, use this config
                        if ($matchCount > 0) {
                            Log::info("Found matching JSON config in seeders: {$file->getFilename()} with {$matchCount} matching words");
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
     * Create FormTemplate from JSON configuration
     */
    private function createTemplateFromJson(ImutData $imutData, array $jsonConfig): void
    {
        $templateData = $jsonConfig['form_template'];

        // Create FormTemplate
        $formTemplate = FormTemplate::create([
            'imut_data_id' => $imutData->id,
            'title' => $templateData['title'],
            'description' => $templateData['description'] ?? "Template untuk {$imutData->title}",
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

        Log::info("FormTemplate created from JSON for ImutData: {$imutData->title}");
    }

    /**
     * Create default Yes/No template when no JSON config is found
     */
    private function createDefaultYesNoTemplate(ImutData $imutData): void
    {
        // Create FormTemplate
        $formTemplate = FormTemplate::create([
            'imut_data_id' => $imutData->id,
            'title' => "Form {$imutData->title}",
            'description' => "Template default untuk {$imutData->title} dengan pilihan Ya/Tidak",
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

        Log::info("Default Yes/No FormTemplate created for ImutData: {$imutData->title}");
    }
}
