<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CompleteFormTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Complete FormTemplate and Data Population...');

        // Step 1: Ensure all profiles have proper FormTemplates with JSON configs
        $this->ensureFormTemplatesWithConfig();

        // Step 2: Populate sample data for all profiles
        $this->populateComprehensiveData();

        $this->command->info('✅ Complete FormTemplate and Data Population finished!');
    }

    /**
     * Ensure all ImutProfiles have FormTemplates with proper JSON configurations
     */
    private function ensureFormTemplatesWithConfig(): void
    {
        $this->command->info('📋 Ensuring FormTemplates with JSON configurations...');

        $profiles = ImutProfile::with('imutData')->get();
        $created = 0;
        $updated = 0;

        foreach ($profiles as $profile) {
            $imutDataTitle = strtolower($profile->imutData->title ?? '');
            $configData = $this->loadFormConfiguration($imutDataTitle);

            if (!$configData) {
                $this->command->warn("⚠️  No configuration found for: {$imutDataTitle}");
                continue;
            }

            // Check if FormTemplate exists
            $formTemplate = FormTemplate::where('imut_profile_id', $profile->id)->first();

            if ($formTemplate) {
                // Update existing template with proper config
                $formTemplate->update([
                    'scoring_config' => json_encode(['form_fields' => $configData['form_fields']])
                ]);
                $updated++;
            } else {
                // Create new FormTemplate
                $template = $configData['form_template'];
                $formTemplate = FormTemplate::create([
                    'imut_profile_id' => $profile->id,
                    'title' => $template['title'] . ' - ' . $profile->version,
                    'description' => $template['description'],
                    'compliance_method' => $template['compliance_method'],
                    'auto_fail_on_critical' => $template['auto_fail_on_critical'],
                    'scoring_config' => json_encode(['form_fields' => $configData['form_fields']])
                ]);

                // Create form fields from JSON configuration
                $this->createFormFieldsFromConfig($formTemplate, $configData['form_fields']);
                $created++;
            }
        }

        $this->command->info("✅ FormTemplates created: {$created}");
        $this->command->info("🔄 FormTemplates updated: {$updated}");
    }

    /**
     * Load form configuration from JSON file
     */
    private function loadFormConfiguration(string $imutDataTitle): ?array
    {
        $configFile = null;

        if ($this->containsKeywords($imutDataTitle, ['cuci tangan', 'hand hygiene', 'kebersihan tangan'])) {
            $configFile = 'kepatuhan-kebersihan-tangan.json';
        } elseif ($this->containsKeywords($imutDataTitle, ['apd', 'alat pelindung', 'protective equipment'])) {
            $configFile = 'kepatuhan-penggunaan-apd.json';
        } elseif ($this->containsKeywords($imutDataTitle, ['identifikasi pasien', 'patient identification'])) {
            $configFile = 'ketepatan-identifikasi-pasien.json';
        } elseif ($this->containsKeywords($imutDataTitle, ['jatuh', 'fall', 'risiko jatuh'])) {
            $configFile = 'pencegahan-risiko-jatuh.json';
        }

        if (!$configFile) {
            return null;
        }

        $configPath = database_path('data/form-configurations/' . $configFile);

        if (!File::exists($configPath)) {
            return null;
        }

        $jsonContent = File::get($configPath);
        return json_decode($jsonContent, true);
    }

    /**
     * Check if title contains keywords
     */
    private function containsKeywords(string $title, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($title, strtolower($keyword))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create form fields from JSON configuration
     */
    private function createFormFieldsFromConfig(FormTemplate $formTemplate, array $formFields): void
    {
        foreach ($formFields as $fieldData) {
            // Create EnhancedFormField
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
                'conditional_logic' => isset($fieldData['conditional_logic']) ? json_encode($fieldData['conditional_logic']) : null,
                'compliance_rules' => isset($fieldData['compliance_rules']) ? json_encode($fieldData['compliance_rules']) : null,
            ]);

            // Create options if available
            if (isset($fieldData['options']) && is_array($fieldData['options'])) {
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

    /**
     * Populate comprehensive data for all FormTemplates
     */
    private function populateComprehensiveData(): void
    {
        $this->command->info('📊 Populating comprehensive sample data...');

        $formTemplates = FormTemplate::with(['imutProfile.imutData'])->get();
        $users = User::limit(10)->get();
        $units = \App\Models\UnitKerja::limit(5)->get();

        if ($users->isEmpty()) {
            $this->command->error('❌ No users found. Please seed users first.');
            return;
        }

        if ($units->isEmpty()) {
            $this->command->error('❌ No units found. Please seed units first.');
            return;
        }

        $totalReports = 0;
        $totalResponses = 0;

        foreach ($formTemplates as $template) {
            $imutDataTitle = strtolower($template->imutProfile->imutData->title ?? '');

            // Skip if already has data
            if (DailyReportResponse::where('form_template_id', $template->id)->exists()) {
                continue;
            }

            // Create 7 days of historical data
            for ($day = 6; $day >= 0; $day--) {
                $reportDate = Carbon::now()->subDays($day);
                $user = $users->random();

                $dailyReport = DailyReportResponse::create([
                    'form_template_id' => $template->id,
                    'unit_kerja_id' => $units->random()->id,
                    'submitted_by' => $user->id,
                    'report_date' => $reportDate,
                    'compliance_status' => $this->getRandomComplianceStatus(),
                    'total_score' => rand(75, 98),
                    'notes' => "Data sample untuk {$template->imutProfile->imutData->title}",
                    'auto_calculated' => true,
                    'calculation_details' => ['source' => 'seeder'],
                ]);

                // Create field responses based on form configuration
                $fieldResponses = $this->createFieldResponsesForFormType($imutDataTitle, $dailyReport->id, $template);
                $totalResponses += count($fieldResponses);
                $totalReports++;
            }

            $this->command->info("✅ Created data for {$template->imutProfile->imutData->title}");
        }

        $this->command->info("📈 Total Daily Reports created: {$totalReports}");
        $this->command->info("📋 Total Field Responses created: {$totalResponses}");
    }

    /**
     * Create field responses based on form configuration from JSON
     */
    private function createFieldResponsesForFormType(string $formType, int $dailyReportId, FormTemplate $template): array
    {
        $responses = [];
        $scoringConfig = json_decode($template->scoring_config, true);

        if (!isset($scoringConfig['form_fields'])) {
            return $responses;
        }

        foreach ($scoringConfig['form_fields'] as $fieldConfig) {
            $field = $template->fields()->where('field_key', $fieldConfig['field_key'])->first();

            if (!$field) {
                continue;
            }

            $fieldValue = $this->generateSampleValueForField($fieldConfig, $field);
            if ($fieldValue !== null) {
                $responses[] = FieldResponse::create([
                    'daily_report_response_id' => $dailyReportId,
                    'form_field_id' => $field->id,
                    'field_value' => $fieldValue
                ]);
            }
        }

        return $responses;
    }

    /**
     * Generate sample value for a field based on its configuration
     */
    private function generateSampleValueForField(array $fieldConfig, $field): ?string
    {
        $fieldType = $fieldConfig['field_type'] ?? 'text';

        switch ($fieldType) {
            case 'single_select':
                return $this->generateSingleSelectValue($fieldConfig);

            case 'multi_select':
                return $this->generateMultiSelectValue($fieldConfig);

            case 'boolean':
                return rand(0, 1) ? 'true' : 'false';

            case 'number':
                $validation = $fieldConfig['validation_config'] ?? [];
                $min = $validation['min'] ?? 0;
                $max = $validation['max'] ?? 100;
                return (string) rand($min, $max);

            case 'rating_scale':
                $scale = $fieldConfig['validation_config']['scale'] ?? '1-5';
                if (preg_match('/(\d+)-(\d+)/', $scale, $matches)) {
                    return (string) rand((int)$matches[1], (int)$matches[2]);
                }
                return '3';

            case 'short_text':
            case 'text':
                return $this->generateTextValue($fieldConfig);

            default:
                return 'Sample data';
        }
    }

    /**
     * Generate value for single select field
     */
    private function generateSingleSelectValue(array $fieldConfig): ?string
    {
        if (!isset($fieldConfig['options']) || empty($fieldConfig['options'])) {
            return null;
        }

        // Prioritize correct options for compliance simulation
        $correctOptions = array_filter($fieldConfig['options'], fn($opt) => $opt['is_correct'] ?? false);
        $wrongOptions = array_filter($fieldConfig['options'], fn($opt) => !($opt['is_correct'] ?? false));

        // 70% chance to pick correct option, 30% chance to pick wrong
        if (!empty($correctOptions) && rand(1, 10) <= 7) {
            $selectedOption = $correctOptions[array_rand($correctOptions)];
        } elseif (!empty($wrongOptions)) {
            $selectedOption = $wrongOptions[array_rand($wrongOptions)];
        } else {
            $selectedOption = $fieldConfig['options'][array_rand($fieldConfig['options'])];
        }

        return $selectedOption['option_value'];
    }

    /**
     * Generate value for multi select field
     */
    private function generateMultiSelectValue(array $fieldConfig): ?string
    {
        if (!isset($fieldConfig['options']) || empty($fieldConfig['options'])) {
            return null;
        }

        $options = $fieldConfig['options'];
        $selectedValues = [];

        // Always include some correct options if available
        $correctOptions = array_filter($options, fn($opt) => $opt['is_correct'] ?? false);
        if (!empty($correctOptions)) {
            $numCorrect = rand(1, min(3, count($correctOptions)));
            $selectedCorrect = array_rand($correctOptions, $numCorrect);
            if (!is_array($selectedCorrect)) {
                $selectedCorrect = [$selectedCorrect];
            }
            foreach ($selectedCorrect as $index) {
                $selectedValues[] = $correctOptions[$index]['option_value'];
            }
        }

        // Sometimes add wrong options too (simulating partial compliance)
        if (rand(1, 10) <= 3) { // 30% chance
            $wrongOptions = array_filter($options, fn($opt) => !($opt['is_correct'] ?? false));
            if (!empty($wrongOptions)) {
                $numWrong = rand(1, min(2, count($wrongOptions)));
                $selectedWrong = array_rand($wrongOptions, $numWrong);
                if (!is_array($selectedWrong)) {
                    $selectedWrong = [$selectedWrong];
                }
                foreach ($selectedWrong as $index) {
                    $selectedValues[] = $wrongOptions[$index]['option_value'];
                }
            }
        }

        return implode(',', array_unique($selectedValues));
    }

    /**
     * Generate text value for text fields
     */
    private function generateTextValue(array $fieldConfig): string
    {
        $fieldKey = $fieldConfig['field_key'] ?? '';

        // Generate context-aware sample text
        switch ($fieldKey) {
            case 'data_collector':
                return 'Dr. ' . ['Ahmad', 'Siti', 'Budi', 'Maya', 'Rizki'][rand(0, 4)];

            case 'additional_notes':
                return 'Observasi dilakukan sesuai protokol. Temuan akan ditindaklanjuti.';

            default:
                return 'Data observasi telah dicatat dengan baik.';
        }
    }

    /**
     * Get random compliance status
     */
    private function getRandomComplianceStatus(): string
    {
        $statuses = ['excellent', 'good', 'fair', 'poor'];
        $weights = [30, 40, 20, 10]; // Higher chance for good compliance

        $rand = rand(1, 100);
        $cumulative = 0;

        for ($i = 0; $i < count($statuses); $i++) {
            $cumulative += $weights[$i];
            if ($rand <= $cumulative) {
                return $statuses[$i];
            }
        }

        return 'good';
    }
}
