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
     * This seeder replicates profiles that are expired or in the future
     * to make them valid for the current period. Use this ONLY when you need
     * to ensure profiles are valid for daily reporting.
     * 
     * ⚠️ WARNING: This will create duplicate profiles if run after CompleteFormTemplateSeeder
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Valid Daily Report Profile Seeder...');
        $this->command->info('📋 Strategy: Duplicate expired/future profiles with valid dates');
        $this->command->newLine();

        DB::beginTransaction();

        try {
            // Find profiles with form templates that are NOT currently valid
            $profilesToReplicate = ImutProfile::whereHas('formTemplates')
                ->with(['formTemplates.formFields.options', 'imutData'])
                ->where(function ($query) {
                    $query->where('valid_until', '<', now())
                        ->orWhere('valid_from', '>', now());
                })
                ->get();

            if ($profilesToReplicate->isEmpty()) {
                $this->command->info('✅ All profiles with FormTemplates are already valid!');
                $this->command->info('No replication needed.');
                DB::rollBack();
                return;
            }

            $this->command->info("Found {$profilesToReplicate->count()} non-valid profiles");
            $this->command->newLine();

            $replicated = 0;
            $skipped = 0;

            foreach ($profilesToReplicate as $oldProfile) {
                $result = $this->replicateProfile($oldProfile);

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

        // Check if there's already a valid profile for this ImutData
        $existingValid = ImutProfile::where('imut_data_id', $oldProfile->imut_data_id)
            ->where('id', '!=', $oldProfile->id)
            ->where('valid_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->exists();

        if ($existingValid) {
            $this->command->warn("  ⚠️  Valid profile already exists for this ImutData, skipping...");
            return false;
        }

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
            $newProfile->id = null;
            $newProfile->save();

            // Check if FormTemplate already exists for new profile (should not happen, but safety check)
            if (FormTemplate::where('imut_profile_id', $newProfile->id)->exists()) {
                $this->command->warn("  ⚠️  FormTemplate already exists for new profile, skipping template replication...");
                return true; // Profile created successfully, just skip template
            }

            // Replicate form templates
            $oldProfile->formTemplates->each(function ($template) use ($newProfile) {
                // Additional check before creating template
                $existingTemplate = FormTemplate::where('imut_profile_id', $newProfile->id)
                    ->where('title', $template->title)
                    ->first();

                if ($existingTemplate) {
                    return; // Skip this template
                }

                $newTemplate = $template->replicate();
                $newTemplate->imut_profile_id = $newProfile->id;
                $newTemplate->id = null; // Force new ID
                $newTemplate->save();

                // Replicate form fields
                $template->formFields->each(function ($field) use ($newTemplate) {
                    $newField = $field->replicate();
                    $newField->form_template_id = $newTemplate->id;
                    $newField->id = null; // Force new ID
                    $newField->save();

                    // Replicate field options
                    $field->options->each(function ($option) use ($newField) {
                        $newOption = $option->replicate();
                        $newOption->enhanced_form_field_id = $newField->id;
                        $newOption->id = null; // Force new ID
                        $newOption->save();
                    });
                });
            });

            $this->command->line("  ✓ Created new profile (ID: {$newProfile->id})");
            $this->command->line("     Valid from: {$newProfile->valid_from->format('Y-m-d')} to {$newProfile->valid_until->format('Y-m-d')}");

            return true;
        } catch (\Exception $e) {
            $this->command->error("  ❌ Failed: " . $e->getMessage());
            return false;
        }
    }
}
