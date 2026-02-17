<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FieldResponse;
use App\Services\FormBuilder\FormPersistenceService;
use Illuminate\Database\Seeder;

class VerifyHandwashingTemplateEditPreservesResponsesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔬 Verify: editing Handwashing template preserves historical FieldResponse records');

        // Ensure template exists
        $this->callIfExists(HandHygieneFormSeeder::class);

        // Ensure simulation responses exist
        $this->callIfExists(HandwashingSimulationSeeder::class);

        // Find the active ImutProfile for hand hygiene
        $profile = ImutProfile::whereHas('imutData', function ($q) {
            $q->where('title', 'like', '%kepatuhan kebersihan tangan%');
        })->latest()->first();

        if (!$profile) {
            $this->command->error('❌ ImutProfile for Hand Hygiene not found.');
            return;
        }

        $template = FormTemplate::where('imut_profile_id', $profile->id)->latest()->first();
        if (!$template) {
            $this->command->error('❌ FormTemplate for Hand Hygiene not found.');
            return;
        }

        $this->command->info("Using template: {$template->title} (id={$template->id})");

        // Pick a field that will be "removed" in the incoming payload but should be preserved because it has responses
        $targetFieldKey = 'hand_hygiene_indication';
        $existingField = $template->fields()->where('field_key', $targetFieldKey)->first();

        if (!$existingField) {
            $this->command->warn("Field '{$targetFieldKey}' not found on template — cannot validate preservation behavior");
            return;
        }

        $beforeCount = FieldResponse::where('form_field_id', $existingField->id)->count();
        $this->command->info("FieldResponse count for '{$targetFieldKey}' before edit: {$beforeCount}");

        // Prepare an incoming payload that intentionally does NOT include 'hand_hygiene_indication'
        // and that updates the label of 'hand_hygiene_method' and adds a new field
        $payload = [
            'title' => $template->title,
            'description' => $template->description,
            'compliance_method' => $template->compliance_method,
            'auto_fail_on_critical' => $template->auto_fail_on_critical,
            'fields' => [
                [
                    'field_key' => 'hand_hygiene_method',
                    'field_label' => 'Metode Kebersihan Tangan (diedit)',
                    'field_type' => 'single_select',
                    'validation_config' => ['required' => true],
                    'compliance_weight' => 3,
                    'is_critical_field' => true,
                    'options' => [
                        ['option_text' => 'Hand Rub (alcool)', 'option_value' => 'hand_rub', 'is_correct' => true],
                        ['option_text' => 'Air + Sabun', 'option_value' => 'air_sabun', 'is_correct' => true],
                        ['option_text' => 'Tidak cuci tangan', 'option_value' => 'tidak_cuci_tangan', 'is_correct' => false],
                    ],
                ],
                // Keep six_steps_compliance so it remains
                [
                    'field_key' => 'six_steps_compliance',
                    'field_label' => '6 Langkah (tetap)',
                    'field_type' => 'multi_select',
                    'validation_config' => [],
                    'compliance_weight' => 5,
                    'is_critical_field' => true,
                    'options' => [],
                ],
                // Add a new informational field
                [
                    'field_key' => 'observer_notes_new',
                    'field_label' => 'Catatan Pengamat (baru)',
                    'field_type' => 'short_text',
                    'validation_config' => [],
                    'compliance_weight' => 0,
                    'is_critical_field' => false,
                ],
            ],
        ];

        // Execute the form template update via the service (mirrors UI behaviour)
        $service = app(FormPersistenceService::class);
        $service->saveFormData($profile, $payload);

        // Re-fetch the field and count responses after the update
        $existingFieldAfter = EnhancedFormField::where('form_template_id', $template->id)
            ->where('field_key', $targetFieldKey)
            ->first();

        $afterCount = $existingFieldAfter ? FieldResponse::where('form_field_id', $existingFieldAfter->id)->count() : 0;
        $this->command->info("FieldResponse count for '{$targetFieldKey}' after edit: {$afterCount}");

        if ($existingFieldAfter) {
            if ($beforeCount === $afterCount && $afterCount > 0) {
                $this->command->info('✅ Historical FieldResponse rows preserved — field was NOT deleted.');
            } else {
                $this->command->warn('⚠️ FieldResponse counts differ after edit — investigate further.');
            }
        } else {
            $this->command->error('❌ The field was deleted (unexpected) — historical responses may be lost.');
        }

        // Also show that new field was created
        $newField = EnhancedFormField::where('form_template_id', $template->id)
            ->where('field_key', 'observer_notes_new')
            ->first();

        $this->command->info('New field created: ' . ($newField ? 'yes (id=' . $newField->id . ')' : 'no'));

        $this->command->info('🔚 Verification seeder completed.');
    }

    private function callIfExists(string $seederClass): void
    {
        if (class_exists($seederClass)) {
            $this->call($seederClass);
        }
    }
}
