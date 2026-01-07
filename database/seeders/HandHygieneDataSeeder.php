<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HandHygieneDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧼 Creating comprehensive hand hygiene data...');

        // Get all hand hygiene profiles
        $profiles = ImutProfile::whereHas('imutData', function ($q) {
            $q->where('title', 'like', '%kebersihan tangan%');
        })->get();

        $users = User::limit(10)->get();

        if ($users->isEmpty()) {
            $this->command->error('❌ No users found. Please seed users first.');
            return;
        }

        $totalReports = 0;
        $totalResponses = 0;

        $this->command->info("📊 Creating data for {$profiles->count()} hand hygiene profiles...");

        foreach ($profiles as $profile) {
            $template = FormTemplate::where('imut_profile_id', $profile->id)->first();
            if (!$template) {
                $this->command->warn("⚠️  No FormTemplate found for profile {$profile->version}");
                continue;
            }

            // Skip if already has data
            if (DailyReportResponse::where('form_template_id', $template->id)->exists()) {
                $this->command->info("⏭️  Skipping {$profile->version} (already has data)");
                continue;
            }

            // Create 7 days of historical data
            for ($day = 6; $day >= 0; $day--) {
                $reportDate = Carbon::now()->subDays($day);
                $user = $users->random();

                $dailyReport = DailyReportResponse::create([
                    'form_template_id' => $template->id,
                    'submitted_by' => $user->id,
                    'submission_date' => $reportDate,
                    'compliance_status' => $this->getRandomComplianceStatus(),
                    'overall_score' => rand(80, 98),
                    'additional_notes' => "Sample data kebersihan tangan - {$profile->version}",
                    'is_validated' => true,
                    'validated_by' => $user->id,
                    'validated_at' => $reportDate->addHours(2)
                ]);

                // Create field responses based on compliance level
                $complianceLevel = $this->getRandomComplianceLevel();

                // Field 1: Hand hygiene method
                FieldResponse::create([
                    'daily_report_response_id' => $dailyReport->id,
                    'form_field_id' => 1,
                    'field_value' => $complianceLevel['method']
                ]);
                $totalResponses++;

                if ($complianceLevel['method'] !== 'tidak_cuci_tangan') {
                    // Field 2: 5 WHO moments (if method is compliant)
                    $moments = ['sebelum_kontak_pasien', 'sebelum_prosedur_aseptik', 'setelah_risiko_cairan', 'setelah_kontak_pasien', 'setelah_kontak_area_sekitar'];
                    $selectedMoments = array_slice($moments, 0, $complianceLevel['moments_count']);

                    FieldResponse::create([
                        'daily_report_response_id' => $dailyReport->id,
                        'form_field_id' => 2,
                        'field_value' => implode(',', $selectedMoments)
                    ]);
                    $totalResponses++;

                    // Field 3: 6 steps compliance
                    $steps = ['gosok_telapak_tangan', 'gosok_punggung_sela_jari', 'gosok_telapak_sela_jari', 'jari_sisi_dalam_mengunci', 'gosok_ibu_jari_berputar', 'ujung_jari_berputar'];
                    $completedSteps = array_slice($steps, 0, $complianceLevel['steps_count']);

                    FieldResponse::create([
                        'daily_report_response_id' => $dailyReport->id,
                        'form_field_id' => 3,
                        'field_value' => implode(',', $completedSteps)
                    ]);
                    $totalResponses++;
                }

                $totalReports++;
            }

            $this->command->info("✅ Created 7 days data for {$profile->version}");
        }

        $this->command->info('🎉 Summary:');
        $this->command->info("📈 Total Daily Reports: {$totalReports}");
        $this->command->info("📋 Total Field Responses: {$totalResponses}");
        $this->command->info('✅ Hand hygiene data seeding completed!');
    }

    /**
     * Get random compliance status
     */
    private function getRandomComplianceStatus(): string
    {
        $statuses = ['excellent', 'good', 'fair', 'poor'];
        $weights = [30, 40, 20, 10]; // Higher chance for good compliance

        $rand = rand(1, 100);
        $cumulative = 0;

        for ($i = 0; $i < count($statuses); $i++) {
            $cumulative += $weights[$i];
            if ($rand <= $cumulative) {
                return $statuses[$i];
            }
        }

        return 'good';
    }

    /**
     * Get random compliance level for hand hygiene
     */
    private function getRandomComplianceLevel(): array
    {
        $levels = [
            // Excellent compliance
            ['method' => 'hand_rub', 'moments_count' => 5, 'steps_count' => 6],
            ['method' => 'air_sabun', 'moments_count' => 4, 'steps_count' => 6],

            // Good compliance  
            ['method' => 'hand_rub', 'moments_count' => 3, 'steps_count' => 5],
            ['method' => 'air_sabun', 'moments_count' => 3, 'steps_count' => 5],

            // Fair compliance
            ['method' => 'hand_rub', 'moments_count' => 2, 'steps_count' => 4],
            ['method' => 'air_sabun', 'moments_count' => 2, 'steps_count' => 4],

            // Poor compliance
            ['method' => 'tidak_cuci_tangan', 'moments_count' => 0, 'steps_count' => 0]
        ];

        $weights = [25, 20, 20, 15, 10, 5, 5]; // Higher chance for excellent/good compliance

        $rand = rand(1, 100);
        $cumulative = 0;

        for ($i = 0; $i < count($levels); $i++) {
            $cumulative += $weights[$i];
            if ($rand <= $cumulative) {
                return $levels[$i];
            }
        }

        return $levels[0];
    }
}
