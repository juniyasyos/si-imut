<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EnsureFormTemplateSeeder extends Seeder
{
    /**
     * Ensure all ImutProfile have FormTemplate
     */
    public function run(): void
    {
        $this->command->info('🔧 Ensuring all ImutProfile have FormTemplate...');

        // Get all ImutProfile that don't have FormTemplate
        $profilesWithoutTemplate = ImutProfile::whereDoesntHave('formTemplates')->get();

        $this->command->info("Found {$profilesWithoutTemplate->count()} ImutProfile without FormTemplate");

        foreach ($profilesWithoutTemplate as $profile) {
            $this->createFormTemplateForProfile($profile);
        }

        // Also check for current active profiles that should have FormTemplate
        $this->ensureActiveProfilesHaveTemplate();

        $this->command->info('✅ All ImutProfile now have FormTemplate!');
    }

    /**
     * Ensure current active profiles have FormTemplate
     */
    private function ensureActiveProfilesHaveTemplate(): void
    {
        $this->command->info('🔍 Checking active profiles for FormTemplate...');

        $now = now()->toDateString();

        // Get all currently active ImutProfiles
        $activeProfiles = ImutProfile::where('valid_from', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $now);
            })
            ->with(['imutData', 'formTemplates'])
            ->get();

        $this->command->info("Found {$activeProfiles->count()} currently active profiles");

        foreach ($activeProfiles as $profile) {
            if ($profile->formTemplates->isEmpty()) {
                $this->command->warn("Active profile ID {$profile->id} ({$profile->imutData->title}) missing FormTemplate!");
                $this->createFormTemplateForProfile($profile);
            }
        }
    }

    /**
     * Create FormTemplate for a specific ImutProfile
     */
    private function createFormTemplateForProfile(ImutProfile $profile): void
    {
        $this->command->info("Creating FormTemplate for profile ID {$profile->id}: {$profile->imutData->title}");

        // Check if a FormTemplate already exists for this profile
        $existingTemplate = FormTemplate::where('imut_profile_id', $profile->id)->first();
        if ($existingTemplate) {
            $this->command->warn("FormTemplate already exists for profile ID {$profile->id}, skipping");
            return;
        }

        // Start transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Create FormTemplate based on ImutData title patterns
            $formTemplate = $this->createFormTemplateBasedOnTitle($profile);

            DB::commit();

            $this->command->info("✅ Created FormTemplate ID {$formTemplate->id} for profile {$profile->id}");
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error("❌ Failed to create FormTemplate for profile {$profile->id}: {$e->getMessage()}");
            Log::error("FormTemplate creation failed", [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create FormTemplate based on ImutData title patterns
     */
    private function createFormTemplateBasedOnTitle(ImutProfile $profile): FormTemplate
    {
        $title = strtolower($profile->imutData->title);

        // Determine template type based on title keywords
        if ($this->containsKeywords($title, ['cuci tangan', 'hand hygiene', 'kebersihan tangan'])) {
            return $this->createHandHygieneTemplate($profile);
        }

        if ($this->containsKeywords($title, ['apd', 'alat pelindung', 'protective equipment'])) {
            return $this->createAPDTemplate($profile);
        }

        if ($this->containsKeywords($title, ['jatuh', 'fall', 'risiko jatuh'])) {
            return $this->createFallPreventionTemplate($profile);
        }

        if ($this->containsKeywords($title, ['identifikasi pasien', 'patient identification'])) {
            return $this->createPatientIdentificationTemplate($profile);
        }

        if ($this->containsKeywords($title, ['obat', 'medication', 'farmasi'])) {
            return $this->createMedicationSafetyTemplate($profile);
        }

        // Default template for unrecognized patterns
        return $this->createDefaultTemplate($profile);
    }

    /**
     * Check if title contains any of the keywords
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
     * Create Hand Hygiene compliance template
     */
    private function createHandHygieneTemplate(ImutProfile $profile): FormTemplate
    {
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => "Form {$profile->imutData->title} - {$profile->version}",
            'description' => "Template kepatuhan cuci tangan untuk {$profile->imutData->title}",
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => true,
        ]);

        // Observation field
        $observationField = EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'observation_count',
            'field_label' => 'Jumlah Observasi',
            'field_description' => 'Total jumlah observasi yang dilakukan',
            'field_type' => 'number',
            'validation_config' => ['required' => true, 'min' => 1],
            'compliance_weight' => 0, // Info field, no compliance weight
            'order_index' => 1,
        ]);

        // Compliance field
        $complianceField = EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => 'compliance_count',
            'field_label' => 'Jumlah Patuh',
            'field_description' => 'Jumlah observasi yang menunjukkan kepatuhan',
            'field_type' => 'number',
            'validation_config' => ['required' => true, 'min' => 0],
            'compliance_weight' => 10,
            'is_critical_field' => true,
            'order_index' => 2,
            'compliance_rules' => [
                'calculation_method' => 'percentage',
                'numerator_field' => 'compliance_count',
                'denominator_field' => 'observation_count',
                'target_percentage' => 80
            ]
        ]);

        return $formTemplate;
    }

    /**
     * Create APD compliance template
     */
    private function createAPDTemplate(ImutProfile $profile): FormTemplate
    {
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => "Form {$profile->imutData->title} - {$profile->version}",
            'description' => "Template kepatuhan penggunaan APD untuk {$profile->imutData->title}",
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => true,
        ]);

        $apdItems = [
            'masker' => 'Penggunaan Masker',
            'sarung_tangan' => 'Penggunaan Sarung Tangan',
            'gown' => 'Penggunaan Gown/Baju Pelindung',
            'pelindung_mata' => 'Penggunaan Pelindung Mata'
        ];

        $index = 1;
        foreach ($apdItems as $key => $label) {
            $field = EnhancedFormField::create([
                'form_template_id' => $formTemplate->id,
                'field_key' => $key,
                'field_label' => $label,
                'field_description' => "Kepatuhan dalam {strtolower($label)}",
                'field_type' => 'single_select',
                'validation_config' => ['required' => true],
                'compliance_weight' => 2.5, // Total 10 for all 4 items
                'is_critical_field' => true,
                'order_index' => $index++,
            ]);

            // Create Yes/No options
            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => 'Ya, digunakan dengan benar',
                'option_value' => 'ya',
                'is_correct' => true,
                'compliance_value' => 100,
                'order_index' => 1,
            ]);

            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => 'Tidak digunakan/tidak benar',
                'option_value' => 'tidak',
                'is_correct' => false,
                'compliance_value' => 0,
                'order_index' => 2,
            ]);
        }

        return $formTemplate;
    }

    /**
     * Create Fall Prevention template
     */
    private function createFallPreventionTemplate(ImutProfile $profile): FormTemplate
    {
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => "Form {$profile->imutData->title} - {$profile->version}",
            'description' => "Template pencegahan risiko jatuh untuk {$profile->imutData->title}",
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => true,
        ]);

        $preventionItems = [
            'risk_assessment' => 'Asesmen Risiko Jatuh',
            'safety_measures' => 'Implementasi Tindakan Keselamatan',
            'patient_education' => 'Edukasi Pasien',
            'environment_safety' => 'Keamanan Lingkungan'
        ];

        $index = 1;
        foreach ($preventionItems as $key => $label) {
            $field = EnhancedFormField::create([
                'form_template_id' => $formTemplate->id,
                'field_key' => $key,
                'field_label' => $label,
                'field_description' => "Kepatuhan dalam {strtolower($label)}",
                'field_type' => 'single_select',
                'validation_config' => ['required' => true],
                'compliance_weight' => 2.5, // Total 10 for all 4 items
                'is_critical_field' => true,
                'order_index' => $index++,
            ]);

            // Create compliance options
            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => 'Dilakukan sesuai standar',
                'option_value' => 'sesuai',
                'is_correct' => true,
                'compliance_value' => 100,
                'order_index' => 1,
            ]);

            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => 'Tidak dilakukan/tidak sesuai',
                'option_value' => 'tidak_sesuai',
                'is_correct' => false,
                'compliance_value' => 0,
                'order_index' => 2,
            ]);
        }

        return $formTemplate;
    }

    /**
     * Create Patient Identification template
     */
    private function createPatientIdentificationTemplate(ImutProfile $profile): FormTemplate
    {
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => "Form {$profile->imutData->title} - {$profile->version}",
            'description' => "Template identifikasi pasien untuk {$profile->imutData->title}",
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => true,
        ]);

        $identificationItems = [
            'name_verification' => 'Verifikasi Nama Pasien',
            'id_verification' => 'Verifikasi Nomor Identitas',
            'wristband_check' => 'Pengecekan Gelang Identitas',
            'verbal_confirmation' => 'Konfirmasi Verbal'
        ];

        $index = 1;
        foreach ($identificationItems as $key => $label) {
            $field = EnhancedFormField::create([
                'form_template_id' => $formTemplate->id,
                'field_key' => $key,
                'field_label' => $label,
                'field_description' => "Kepatuhan dalam {strtolower($label)}",
                'field_type' => 'single_select',
                'validation_config' => ['required' => true],
                'compliance_weight' => 2.5, // Total 10 for all 4 items
                'is_critical_field' => true,
                'order_index' => $index++,
            ]);

            // Create compliance options
            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => 'Dilakukan dengan benar',
                'option_value' => 'benar',
                'is_correct' => true,
                'compliance_value' => 100,
                'order_index' => 1,
            ]);

            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => 'Tidak dilakukan/salah',
                'option_value' => 'salah',
                'is_correct' => false,
                'compliance_value' => 0,
                'order_index' => 2,
            ]);
        }

        return $formTemplate;
    }

    /**
     * Create Medication Safety template
     */
    private function createMedicationSafetyTemplate(ImutProfile $profile): FormTemplate
    {
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => "Form {$profile->imutData->title} - {$profile->version}",
            'description' => "Template keselamatan obat untuk {$profile->imutData->title}",
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => true,
        ]);

        $safetyItems = [
            'patient_verification' => 'Verifikasi Identitas Pasien',
            'medication_verification' => 'Verifikasi Obat (5 benar)',
            'allergy_check' => 'Pengecekan Alergi',
            'documentation' => 'Dokumentasi Pemberian'
        ];

        $index = 1;
        foreach ($safetyItems as $key => $label) {
            $field = EnhancedFormField::create([
                'form_template_id' => $formTemplate->id,
                'field_key' => $key,
                'field_label' => $label,
                'field_description' => "Kepatuhan dalam {strtolower($label)}",
                'field_type' => 'single_select',
                'validation_config' => ['required' => true],
                'compliance_weight' => 2.5, // Total 10 for all 4 items
                'is_critical_field' => true,
                'order_index' => $index++,
            ]);

            // Create compliance options
            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => 'Dilakukan sesuai prosedur',
                'option_value' => 'sesuai',
                'is_correct' => true,
                'compliance_value' => 100,
                'order_index' => 1,
            ]);

            FormFieldOption::create([
                'enhanced_form_field_id' => $field->id,
                'option_text' => 'Tidak sesuai prosedur',
                'option_value' => 'tidak_sesuai',
                'is_correct' => false,
                'compliance_value' => 0,
                'order_index' => 2,
            ]);
        }

        return $formTemplate;
    }

    /**
     * Create default Yes/No template
     */
    private function createDefaultTemplate(ImutProfile $profile): FormTemplate
    {
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $profile->id,
            'title' => "Form {$profile->imutData->title} - {$profile->version}",
            'description' => "Template default untuk {$profile->imutData->title}",
            'compliance_method' => 'auto_calculate',
            'auto_fail_on_critical' => false,
        ]);

        $field = EnhancedFormField::create([
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
            'enhanced_form_field_id' => $field->id,
            'option_text' => 'Ya, memenuhi standar',
            'option_value' => 'ya',
            'is_correct' => true,
            'compliance_value' => 100,
            'order_index' => 1,
        ]);

        FormFieldOption::create([
            'enhanced_form_field_id' => $field->id,
            'option_text' => 'Tidak memenuhi standar',
            'option_value' => 'tidak',
            'is_correct' => false,
            'compliance_value' => 0,
            'order_index' => 2,
        ]);

        return $formTemplate;
    }
}
