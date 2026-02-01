<?php

namespace Database\Seeders;

use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use App\Models\FormTemplate;
use App\Models\ImutData;
use App\Models\ImutProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ValidDailyReportProfileSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * 
     * This seeder ensures all ImutData have at least one currently valid profile.
     * It replicates the latest profile from expired/future profiles to make them valid.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Valid Daily Report Profile Seeder...');
        $this->command->info('📋 Strategy: Ensure all ImutData have valid profiles');
        $this->command->newLine();

        DB::beginTransaction();

        try {
            // Find all ImutData that have profiles but no currently valid profile
            $imutDatasNeedingValidProfiles = ImutData::whereHas('profiles')
                ->with(['profiles' => function ($q) {
                    $q->with(['formTemplates.formFields.options'])
                        ->orderBy('created_at', 'desc');
                }])
                ->get()
                ->filter(function ($imutData) {
                    // Check if this ImutData has a currently valid profile
                    $hasValidProfile = $imutData->profiles->contains(function ($profile) {
                        return $profile->valid_from <= now()
                            && ($profile->valid_until === null || $profile->valid_until >= now());
                    });
                    return !$hasValidProfile;
                });

            if ($imutDatasNeedingValidProfiles->isEmpty()) {
                $this->command->info('✅ All ImutData already have valid profiles!');
                $this->command->info('No replication needed.');
                DB::rollBack();
                return;
            }

            $this->command->info("Found {$imutDatasNeedingValidProfiles->count()} ImutData without valid profiles");
            $this->command->newLine();

            $replicated = 0;
            $skipped = 0;

            foreach ($imutDatasNeedingValidProfiles as $imutData) {
                // Get the latest profile to replicate
                $latestProfile = $imutData->profiles->first(); // Already ordered by created_at desc

                if (!$latestProfile) {
                    $this->command->warn("⚠️  ImutData '{$imutData->title}' has no profiles to replicate");
                    $skipped++;
                    continue;
                }

                $result = $this->replicateProfile($latestProfile);

                if ($result) {
                    $replicated++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();
            $this->command->newLine();
            $this->command->info("✅ Replication completed!");
            $this->command->info("   Replicated: {$replicated}");
            $this->command->info("   Skipped: {$skipped}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error replicating profiles: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }

    /**
     * Replicate an existing profile with valid dates
     */
    private function replicateProfile(ImutProfile $oldProfile): bool
    {
        $this->command->info("📋 Replicating: {$oldProfile->imutData->title} - {$oldProfile->version}");

        try {
            // Replicate profile (same as ProfilesRelationManager logic)
            $newProfile = $oldProfile->replicate();
            $newProfile->version = "Valid - " . now()->format('Y-m-d H:i');

            $slugBase = Str::slug($newProfile->version);
            $uuid = Str::uuid()->toString();
            $newProfile->slug = "{$slugBase}-{$uuid}";

            // Set valid period to now
            $newProfile->valid_from = now();
            $newProfile->valid_until = now()->addYear();
            unset($newProfile->id);
            $newProfile->save();

            // Clean up any existing templates for this profile (from previous incomplete runs)
            FormTemplate::where('imut_profile_id', $newProfile->id)->delete();

            // Replicate form templates if they exist
            if ($oldProfile->formTemplates->isNotEmpty()) {
                $oldProfile->formTemplates->each(function ($template) use ($newProfile) {
                    $newTemplate = $template->replicate();
                    $newTemplate->imut_profile_id = $newProfile->id;
                    $newTemplate->save();

                    // Replicate form fields
                    $template->formFields->each(function ($field) use ($newTemplate) {
                        $newField = $field->replicate();
                        $newField->form_template_id = $newTemplate->id;
                        $newField->save();

                        // Replicate field options
                        $field->options->each(function ($option) use ($newField) {
                            $newOption = $option->replicate();
                            $newOption->enhanced_form_field_id = $newField->id;
                            $newOption->save();
                        });
                    });
                });
                $this->command->line("     ✓ Replicated {$oldProfile->formTemplates->count()} form template(s)");
            } else {
                $this->command->line("     ⚠️  No form templates to replicate");
            }

            $this->command->line("  ✓ Created new profile (ID: {$newProfile->id})");
            $this->command->line("     Valid from: {$newProfile->valid_from->format('Y-m-d')} to {$newProfile->valid_until->format('Y-m-d')}");

            return true;
        } catch (\Exception $e) {
            $this->command->error("  ❌ Failed: " . $e->getMessage());
            return false;
        }
    }
}
