<?php

namespace Database\Seeders;

use App\Models\ImutData;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class HandHygieneFormSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load JSON data
        $jsonPath = database_path('seeders/data/hand_hygiene_form.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("JSON file not found: {$jsonPath}");
            return;
        }

        $formData = json_decode(File::get($jsonPath), true);

        if (!$formData) {
            $this->command->error("Invalid JSON in hand_hygiene_form.json");
            return;
        }

        DB::beginTransaction();

        try {
            // Find atau create ImutData untuk audit cuci tangan
            $imutData = ImutData::firstOrCreate(
                ['title' => 'Monitoring Kepatuhan Cuci Tangan'],
                [
                    'slug' => 'monitoring-kepatuhan-cuci-tangan',
                    'description' => 'Indikator mutu untuk monitoring dan evaluasi kepatuhan cuci tangan tenaga kesehatan berdasarkan standar WHO',
                    'status' => true,
                    'created_by' => 1, // Adjust sesuai user ID yang ada
                    'imut_kategori_id' => 1, // Adjust sesuai kategori yang ada
                ]
            );

            $this->command->info("ImutData created/found: {$imutData->title}");

            // Create FormTemplate
            $formTemplate = FormTemplate::updateOrCreate(
                ['imut_data_id' => $imutData->id],
                [
                    'title' => $formData['form_template']['title'],
                    'description' => $formData['form_template']['description'],
                    'compliance_method' => $formData['form_template']['compliance_method'],
                    'auto_fail_on_critical' => $formData['form_template']['auto_fail_on_critical'],
                    'scoring_config' => $this->generateScoringConfig($formData['form_fields']),
                ]
            );

            $this->command->info("FormTemplate created: {$formTemplate->title}");

            // Delete existing fields to avoid duplicates
            EnhancedFormField::where('form_template_id', $formTemplate->id)->delete();

            // Create form fields
            foreach ($formData['form_fields'] as $fieldData) {
                $field = EnhancedFormField::create([
                    'form_template_id' => $formTemplate->id,
                    'field_key' => $fieldData['field_key'],
                    'field_label' => $fieldData['field_label'],
                    'field_description' => $fieldData['field_description'],
                    'field_type' => $fieldData['field_type'],
                    'validation_config' => $fieldData['validation_config'],
                    'compliance_weight' => $fieldData['compliance_weight'],
                    'is_critical_field' => $fieldData['is_critical_field'],
                    'conditional_logic' => $fieldData['conditional_logic'] ?? null,
                    'compliance_rules' => $fieldData['compliance_rules'] ?? null,
                    'order_index' => $fieldData['order_index'],
                ]);

                $this->command->info("  └─ Field created: {$field->field_label}");

                // Create field options if any
                if (!empty($fieldData['options'])) {
                    foreach ($fieldData['options'] as $index => $optionData) {
                        FormFieldOption::create([
                            'enhanced_form_field_id' => $field->id,
                            'option_text' => $optionData['option_text'],
                            'option_value' => $optionData['option_value'],
                            'is_correct' => $optionData['is_correct'] ?? true,
                            'order_index' => $index + 1,
                        ]);
                    }
                    $this->command->info("     └─ Options created: " . count($fieldData['options']) . " options");
                }
            }

            DB::commit();

            $this->command->info("✅ Hand Hygiene Form seeder completed successfully!");
            $this->command->info("   Form Template ID: {$formTemplate->id}");
            $this->command->info("   Total Fields: " . count($formData['form_fields']));
            $this->command->info("   ImutData: {$imutData->title} (ID: {$imutData->id})");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Seeder failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate scoring configuration for compliance calculation
     */
    private function generateScoringConfig(array $fields): array
    {
        $totalWeight = 0;
        $criticalFields = [];

        foreach ($fields as $field) {
            $totalWeight += $field['compliance_weight'];

            if ($field['is_critical_field']) {
                $criticalFields[] = $field['field_key'];
            }
        }

        return [
            'method' => 'weighted_average',
            'total_weight' => $totalWeight,
            'critical_fields' => $criticalFields,
            'auto_fail_on_critical' => true,
            'passing_score' => 80, // 80% untuk dianggap compliant
            'rules' => [
                'hand_hygiene_method' => [
                    'rule' => 'if_value_equals',
                    'value' => 'tidak_cuci_tangan',
                    'action' => 'auto_fail'
                ],
                'six_steps_compliance' => [
                    'rule' => 'all_options_required',
                    'description' => 'Semua 6 langkah harus dilakukan untuk compliance penuh',
                    'conditional' => [
                        'depends_on' => 'hand_hygiene_method',
                        'when_not' => 'tidak_cuci_tangan'
                    ]
                ]
            ]
        ];
    }
}
