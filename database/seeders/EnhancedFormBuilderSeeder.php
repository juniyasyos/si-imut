<?php

namespace Database\Seeders;

use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use App\Models\ImutData;
use Illuminate\Database\Seeder;

class EnhancedFormBuilderSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Seeding Enhanced Form Builder Data...');

        // Get existing IMUT Data
        $imutData = ImutData::first();
        if (!$imutData) {
            $this->command->error('No IMUT Data found. Please run ImutDataSeeder first.');
            return;
        }

        // Create Handwashing Compliance Form Template
        $this->createHandwashingComplianceForm($imutData);

        // Create Medication Safety Form Template
        $this->createMedicationSafetyForm($imutData);

        $this->command->info('✅ Enhanced Form Builder seeding completed!');
    }

    private function createHandwashingComplianceForm(ImutData $imutData): void
    {
        $this->command->info('📋 Creating Handwashing Compliance Form...');

        $template = FormTemplate::create([
            'imut_data_id' => $imutData->id,
            'title' => 'Monitoring Kepatuhan Cuci Tangan',
            'description' => 'Form untuk monitoring indikator mutu kepatuhan cuci tangan staff medis dengan sistem auto-compliance calculation',
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => true,
            'is_active' => true,
        ]);

        // Field 1: Data Collector (Info field - no compliance weight)
        $collector = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'data_collector',
            'field_name' => 'Pengumpul Data',
            'field_description' => 'Nama petugas yang melakukan observasi kepatuhan cuci tangan',
            'field_type' => 'short_text',
            'validation_config' => ['required' => true, 'max_length' => 100],
            'compliance_weight' => 0.0,
            'is_critical_field' => false,
            'order_index' => 1,
        ]);

        // Field 2: Handwashing Compliance (Critical field with high weight)
        $handwashing = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'handwashing_compliance',
            'field_name' => 'Kepatuhan Cuci Tangan 5 Momen WHO',
            'field_description' => 'Persentase kepatuhan berdasarkan observasi langsung terhadap 5 momen cuci tangan WHO',
            'field_type' => 'single_select',
            'validation_config' => ['required' => true, 'min_observations' => 10],
            'compliance_weight' => 2.0,
            'is_critical_field' => true,
            'order_index' => 2,
        ]);

        // Options for handwashing compliance
        FormFieldOption::create([
            'enhanced_form_field_id' => $handwashing->id,
            'option_text' => 'Sangat Baik (≥95%)',
            'option_value' => 'excellent',
            'compliance_value' => 2,
            'order_index' => 1,
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $handwashing->id,
            'option_text' => 'Baik (80-94%)',
            'option_value' => 'good',
            'compliance_value' => 1,
            'order_index' => 2,
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $handwashing->id,
            'option_text' => 'Kurang (<80%)',
            'option_value' => 'poor',
            'compliance_value' => 0,
            'order_index' => 3,
        ]);

        // Field 3: Total Observations
        $observations = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'total_observations',
            'field_name' => 'Jumlah Total Observasi',
            'field_description' => 'Total jumlah observasi yang dilakukan dalam periode pelaporan',
            'field_type' => 'number',
            'validation_config' => ['required' => true, 'min' => 1, 'max' => 1000],
            'compliance_weight' => 0.5,
            'is_critical_field' => false,
            'order_index' => 3,
        ]);

        // Field 4: Validation Status
        $validation = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'validation_status',
            'field_name' => 'Status Validasi Data',
            'field_description' => 'Konfirmasi validasi data oleh supervisor/koordinator',
            'field_type' => 'boolean',
            'validation_config' => ['required' => true],
            'compliance_weight' => 1.0,
            'is_critical_field' => false,
            'order_index' => 4,
        ]);

        // Options for validation
        FormFieldOption::create([
            'enhanced_form_field_id' => $validation->id,
            'option_text' => 'Valid - Data telah diverifikasi',
            'option_value' => 'true',
            'compliance_value' => 1,
            'order_index' => 1,
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $validation->id,
            'option_text' => 'Tidak Valid - Perlu observasi ulang',
            'option_value' => 'false',
            'compliance_value' => 0,
            'order_index' => 2,
        ]);

        // Field 5: Additional Notes (Conditional field - appears when compliance is poor)
        $notes = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'additional_notes',
            'field_name' => 'Catatan Tambahan',
            'field_description' => 'Catatan tindak lanjut untuk kasus kepatuhan rendah',
            'field_type' => 'long_text',
            'validation_config' => ['required' => false, 'max_length' => 500],
            'compliance_weight' => 0.0,
            'is_critical_field' => false,
            'parent_field_id' => $handwashing->id,
            'condition_value' => 'poor',
            'order_index' => 5,
        ]);

        $this->command->info("   ✅ Created: {$template->title} with {$template->fields()->count()} fields");
    }

    private function createMedicationSafetyForm(ImutData $imutData): void
    {
        $this->command->info('📋 Creating Medication Safety Form...');

        $template = FormTemplate::create([
            'imut_data_id' => $imutData->id,
            'title' => 'Monitoring Keselamatan Penggunaan Obat',
            'description' => 'Form untuk monitoring indikator mutu keselamatan penggunaan obat dengan 6 benar',
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => true,
            'is_active' => true,
        ]);

        // Field 1: Data Collector
        $collector = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'data_collector',
            'field_name' => 'Petugas Pengumpul Data',
            'field_description' => 'Nama petugas farmasi/perawat yang melakukan observasi',
            'field_type' => 'short_text',
            'validation_config' => ['required' => true],
            'compliance_weight' => 0.0,
            'is_critical_field' => false,
            'order_index' => 1,
        ]);

        // Field 2: 6 Rights Compliance
        $sixRights = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'six_rights_compliance',
            'field_name' => 'Kepatuhan 6 Benar Pemberian Obat',
            'field_description' => 'Evaluasi kepatuhan terhadap 6 benar: pasien, obat, dosis, waktu, cara, dokumentasi',
            'field_type' => 'rating_scale',
            'validation_config' => ['required' => true, 'scale' => '1-5'],
            'compliance_weight' => 2.5,
            'is_critical_field' => true,
            'order_index' => 2,
        ]);

        // Rating options for 6 rights
        for ($i = 1; $i <= 5; $i++) {
            $descriptions = [
                1 => 'Sangat Kurang (0-20%)',
                2 => 'Kurang (21-40%)',
                3 => 'Cukup (41-60%)',
                4 => 'Baik (61-80%)',
                5 => 'Sangat Baik (81-100%)'
            ];

            FormFieldOption::create([
                'enhanced_form_field_id' => $sixRights->id,
                'option_text' => $descriptions[$i],
                'option_value' => (string)$i,
                'compliance_value' => $i >= 4 ? 2 : ($i >= 3 ? 1 : 0),
                'order_index' => $i,
            ]);
        }

        // Field 3: Medication Error Count
        $errors = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'medication_errors',
            'field_name' => 'Jumlah Medication Error',
            'field_description' => 'Jumlah kejadian medication error dalam periode pelaporan',
            'field_type' => 'number',
            'validation_config' => ['required' => true, 'min' => 0],
            'compliance_weight' => 1.5,
            'is_critical_field' => true,
            'order_index' => 3,
        ]);

        // Field 4: Validation Status
        $validation = EnhancedFormField::create([
            'form_template_id' => $template->id,
            'field_key' => 'validation_status',
            'field_name' => 'Status Validasi Supervisor',
            'field_description' => 'Verifikasi data oleh supervisor farmasi',
            'field_type' => 'boolean',
            'validation_config' => ['required' => true],
            'compliance_weight' => 1.0,
            'is_critical_field' => false,
            'order_index' => 4,
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $validation->id,
            'option_text' => 'Tervalidasi',
            'option_value' => 'true',
            'compliance_value' => 1,
            'order_index' => 1,
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $validation->id,
            'option_text' => 'Belum Tervalidasi',
            'option_value' => 'false',
            'compliance_value' => 0,
            'order_index' => 2,
        ]);

        $this->command->info("   ✅ Created: {$template->title} with {$template->fields()->count()} fields");
    }
}
