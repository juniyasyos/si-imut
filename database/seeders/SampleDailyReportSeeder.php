<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\DailyReportEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SampleDailyReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Generating sample daily report entries...');

        // Get first 15 valid profiles
        $profiles = ImutProfile::whereDate('valid_from', '<=', now())
            ->whereDate('valid_until', '>=', now())
            ->with(['formTemplates', 'imutData'])
            ->take(15)
            ->get();

        $user = User::first();
        $count = 0;

        foreach ($profiles as $profile) {
            $template = $profile->formTemplates->first();
            if (!$template) continue;

            $imutDataTitle = $profile->imutData->title ?? 'Unknown';
            $this->command->info("   📝 Creating entries for: {$imutDataTitle}");

            // Create 10 entries for each profile (last 20 days)
            for ($i = 0; $i < 10; $i++) {
                $complianceScore = $this->generateComplianceScore();

                $entry = DailyReportEntry::create([
                    'form_template_id' => $template->id,
                    'unit_kerja_id' => $user->unit_kerja_id ?? 1,
                    'submitted_by' => $user->id,
                    'report_date' => now()->subDays(rand(1, 20))->format('Y-m-d'),
                    'entry_time' => now()->subDays(rand(1, 20))->format('H:i:s'),
                    'responses' => json_encode([
                        'status_kepatuhan' => $complianceScore == 100 ? 'patuh' : 'tidak_patuh',
                        'compliance_score' => $complianceScore
                    ])
                ]);
                $count++;
            }
        }

        $this->command->info("✅ Created {$count} daily report entries");
    }

    /**
     * Generate realistic compliance score
     */
    private function generateComplianceScore(): float
    {
        $rand = rand(1, 100);

        // 40% excellent (100%)
        if ($rand <= 40) return 100.0;

        // 35% good (90-99%)
        if ($rand <= 75) return rand(90, 99);

        // 20% poor (70-89%)
        if ($rand <= 95) return rand(70, 89);

        // 5% very poor (50-69%)
        return rand(50, 69);
    }
}
