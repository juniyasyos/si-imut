<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyReportEntry;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;

class SimpleDailyReportEntrySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating sample DailyReportEntry data...');

        // Get valid FormTemplates only (with scoring_config and valid ImutProfile)
        $validFormTemplates = FormTemplate::whereNotNull('scoring_config')
            ->whereHas('imutProfile', function ($q) {
                $now = now();
                $q->where('valid_from', '<=', $now)
                    ->where('valid_until', '>=', $now);
            })
            ->with('imutProfile.imutData.unitKerja')
            ->take(5)
            ->get();

        if ($validFormTemplates->isEmpty()) {
            $this->command->warn('⚠️ No valid FormTemplates found. Run CompleteFormTemplateSeeder first.');
            return;
        }

        $users = User::take(3)->get();
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No users found. Please run user seeders first.');
            return;
        }

        $entriesCreated = 0;

        foreach ($validFormTemplates as $template) {
            $unitKerjas = $template->imutProfile->imutData->unitKerja ?? collect();
            $unitKerja = $unitKerjas->first() ?? UnitKerja::first();

            if (!$unitKerja) {
                continue;
            }

            // Create entries for the last 7 days
            for ($day = 6; $day >= 0; $day--) {
                $reportDate = Carbon::now()->subDays($day);

                // Check if entry already exists
                if (DailyReportEntry::where('form_template_id', $template->id)
                    ->where('report_date', $reportDate->format('Y-m-d'))
                    ->exists()
                ) {
                    continue;
                }

                $entry = DailyReportEntry::create([
                    'form_template_id' => $template->id,
                    'unit_kerja_id' => $unitKerja->id,
                    'report_date' => $reportDate,
                    'responses' => $this->generateSampleResponses($template),
                ]);

                $entriesCreated++;
            }
        }

        $this->command->info("✅ Created {$entriesCreated} sample DailyReportEntry records.");
    }

    private function generateSampleResponses($template): array
    {
        $responses = [];
        $config = $template->scoring_config;

        if (is_array($config) && isset($config['fields'])) {
            foreach ($config['fields'] as $field) {
                $fieldName = $field['name'] ?? $field['label'] ?? 'field_' . uniqid();
                $responses[$fieldName] = rand(80, 98);
            }
        } else {
            // Fallback simple responses
            $responses = [
                'target_value' => rand(80, 98),
                'actual_value' => rand(75, 95),
                'compliance_percentage' => rand(85, 98),
                'notes' => 'Sample data entry'
            ];
        }

        return $responses;
    }
}
