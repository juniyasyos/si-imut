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
                    'title' => 'Template Observasi: ' . $imutDataTitle,
                    'description' => 'Form observasi cerdas dengan conditional logic untuk ' . $imutDataTitle . ' - v' . $profile->version,
                    'compliance_method' => 'weighted_average',
                    'auto_fail_on_critical' => true,
                    'scoring_config' => json_encode([
                        'method' => 'weighted_average',
                        'critical_fields_auto_fail' => true,
                        'partial_compliance_allowed' => true,
                        'form_fields' => [
                            [
                                'field_name' => 'status_observasi',
                                'compliance_weight' => 100,
                                'is_critical' => true
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
     * Create default form fields for a template - More complex but concise
     */
    private function createDefaultFields(FormTemplate $formTemplate): void
    {
        // Field 1: Status Observasi (Radio with complex compliance logic)
        $statusField = EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'status_observasi',
            'field_label' => 'Status Observasi',
            'field_description' => 'Pilih status hasil observasi indikator ini',
            'field_type' => 'radio',
            'validation_config' => json_encode([
                'required' => true,
                'conditional_fields' => [
                    'tidak_patuh' => ['alasan_tidak_patuh', 'tindakan_perbaikan'],
                    'perlu_perbaikan' => ['rencana_perbaikan', 'timeline']
                ]
            ]),
            'compliance_weight' => 100,
            'is_critical_field' => true,
            'compliance_rules' => json_encode([
                'compliant_values' => ['patuh'],
                'partial_compliant_values' => ['perlu_perbaikan'],
                'non_compliant_values' => ['tidak_patuh'],
                'scoring_logic' => 'weighted_average',
                'critical_fail_threshold' => 0
            ]),
            'order_index' => 1
        ]);

        // Options for status observasi with complex scoring
        FormFieldOption::create([
            'enhanced_form_field_id' => $statusField->id,
            'option_text' => '✅ Patuh - Memenuhi semua standar',
            'option_value' => 'patuh',
            'is_correct' => true,
            'compliance_value' => 100,
            'order_index' => 1
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $statusField->id,
            'option_text' => '⚠️ Perlu Perbaikan - Memerlukan tindakan korektif',
            'option_value' => 'perlu_perbaikan',
            'is_correct' => false,
            'compliance_value' => 50,
            'order_index' => 2
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $statusField->id,
            'option_text' => '❌ Tidak Patuh - Pelanggaran kriteria',
            'option_value' => 'tidak_patuh',
            'is_correct' => false,
            'compliance_value' => 0,
            'order_index' => 3
        ]);

        // Conditional Field 2: Alasan Tidak Patuh (Textarea - only shown when status = tidak_patuh)
        EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'alasan_tidak_patuh',
            'field_label' => 'Alasan Ketidakpatuhan',
            'field_description' => 'Jelaskan secara detail alasan indikator ini tidak patuh',
            'field_type' => 'textarea',
            'validation_config' => json_encode([
                'required' => true,
                'min_length' => 10,
                'max_length' => 500,
                'conditional_show' => 'status_observasi:tidak_patuh'
            ]),
            'compliance_weight' => 0,
            'is_critical_field' => false,
            'order_index' => 2
        ]);

        // Conditional Field 3: Tindakan Perbaikan (Textarea - only shown when status = tidak_patuh)
        EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'tindakan_perbaikan',
            'field_label' => 'Tindakan Perbaikan',
            'field_description' => 'Jelaskan tindakan korektif yang akan dilakukan',
            'field_type' => 'textarea',
            'validation_config' => json_encode([
                'required' => true,
                'min_length' => 20,
                'max_length' => 1000,
                'conditional_show' => 'status_observasi:tidak_patuh'
            ]),
            'compliance_weight' => 0,
            'is_critical_field' => false,
            'order_index' => 3
        ]);

        // Conditional Field 4: Rencana Perbaikan (Textarea - only shown when status = perlu_perbaikan)
        EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'rencana_perbaikan',
            'field_label' => 'Rencana Perbaikan',
            'field_description' => 'Jelaskan rencana perbaikan yang akan dilakukan',
            'field_type' => 'textarea',
            'validation_config' => json_encode([
                'required' => true,
                'min_length' => 15,
                'max_length' => 800,
                'conditional_show' => 'status_observasi:perlu_perbaikan'
            ]),
            'compliance_weight' => 0,
            'is_critical_field' => false,
            'order_index' => 4
        ]);

        // Conditional Field 5: Timeline Perbaikan (Date - only shown when status = perlu_perbaikan)
        EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'timeline_perbaikan',
            'field_label' => 'Target Waktu Perbaikan',
            'field_description' => 'Tanggal target penyelesaian perbaikan',
            'field_type' => 'date',
            'validation_config' => json_encode([
                'required' => true,
                'future_date_only' => true,
                'conditional_show' => 'status_observasi:perlu_perbaikan'
            ]),
            'compliance_weight' => 0,
            'is_critical_field' => false,
            'order_index' => 5
        ]);
    }
}
