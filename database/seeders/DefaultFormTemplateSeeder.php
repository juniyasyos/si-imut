<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DefaultFormTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating default FormTemplates for profiles without templates...');

        DB::transaction(function () {
            // Get all valid profiles without FormTemplates
            $profilesWithoutTemplates = ImutProfile::whereDate('valid_from', '<=', now())
                ->whereDate('valid_until', '>=', now())
                ->doesntHave('formTemplates')
                ->with('imutData')
                ->get();

            if ($profilesWithoutTemplates->isEmpty()) {
                $this->command->info('✅ All profiles already have FormTemplates!');
                return;
            }

            $this->command->info("📊 Found {$profilesWithoutTemplates->count()} profiles without FormTemplates");

            $created = 0;
            foreach ($profilesWithoutTemplates as $profile) {
                $imutDataTitle = $profile->imutData->title ?? 'Unknown';

                // Create default FormTemplate
                $formTemplate = FormTemplate::create([
                    'imut_profile_id' => $profile->id,
                    'title' => $imutDataTitle . ' - ' . $profile->version,
                    'description' => 'Form untuk ' . $imutDataTitle,
                    'compliance_method' => 'threshold',
                    'auto_fail_on_critical' => false,
                    'scoring_config' => json_encode([
                        'form_fields' => [
                            [
                                'field_name' => 'status_kepatuhan',
                                'compliance_weight' => 100
                            ]
                        ]
                    ])
                ]);

                // Create default fields
                $this->createDefaultFields($formTemplate);

                $created++;
                if ($created % 20 == 0) {
                    $this->command->info("   ⏳ Progress: {$created}/{$profilesWithoutTemplates->count()}");
                }
            }

            $this->command->info("✅ Created {$created} default FormTemplates");
        });
    }

    /**
     * Create default form fields for a template
     */
    private function createDefaultFields(FormTemplate $formTemplate): void
    {
        // Field 1: Status Kepatuhan (Radio with options)
        $statusField = EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'status_kepatuhan',
            'field_label' => 'Status Kepatuhan',
            'field_description' => 'Pilih status kepatuhan',
            'field_type' => 'radio',
            'validation_config' => json_encode(['required' => true]),
            'compliance_weight' => 100,
            'is_critical_field' => false,
            'compliance_rules' => json_encode([
                'compliant_values' => ['patuh'],
                'non_compliant_values' => ['tidak_patuh']
            ]),
            'order_index' => 1
        ]);

        // Options for status kepatuhan
        FormFieldOption::create([
            'enhanced_form_field_id' => $statusField->id,
            'option_text' => 'Patuh',
            'option_value' => 'patuh',
            'is_correct' => true,
            'compliance_value' => 100,
            'order_index' => 1
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $statusField->id,
            'option_text' => 'Tidak Patuh',
            'option_value' => 'tidak_patuh',
            'is_correct' => false,
            'compliance_value' => 0,
            'order_index' => 2
        ]);

        // Field 2: Tanggal Observasi
        EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'tanggal_observasi',
            'field_label' => 'Tanggal Observasi',
            'field_description' => 'Tanggal pelaksanaan observasi',
            'field_type' => 'date',
            'validation_config' => json_encode(['required' => true]),
            'compliance_weight' => 0,
            'is_critical_field' => false,
            'order_index' => 2
        ]);

        // Field 3: Waktu Observasi
        EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'waktu_observasi',
            'field_label' => 'Waktu Observasi',
            'field_description' => 'Waktu pelaksanaan observasi',
            'field_type' => 'time',
            'validation_config' => json_encode(['required' => true]),
            'compliance_weight' => 0,
            'is_critical_field' => false,
            'time_format' => 'HH:mm',
            'order_index' => 3
        ]);

        // Field 4: Catatan
        EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'catatan',
            'field_label' => 'Catatan',
            'field_description' => 'Catatan tambahan (opsional)',
            'field_type' => 'textarea',
            'validation_config' => json_encode(['required' => false]),
            'compliance_weight' => 0,
            'is_critical_field' => false,
            'order_index' => 4
        ]);
    }
}
