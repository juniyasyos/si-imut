<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use Illuminate\Database\Seeder;

class FixHandHygieneResponsesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔧 Fixing missing field responses for hand hygiene profiles...');

        // Get all hand hygiene profiles
        $profiles = ImutProfile::whereHas('imutData', function ($q) {
            $q->where('title', 'like', '%kebersihan tangan%');
        })->get();

        $fixed = 0;
        $totalResponses = 0;

        foreach ($profiles as $profile) {
            $template = FormTemplate::where('imut_profile_id', $profile->id)->first();
            if (!$template) {
                continue;
            }

            $reports = DailyReportResponse::where('form_template_id', $template->id)->get();

            foreach ($reports as $report) {
                $existingResponses = FieldResponse::where('daily_report_response_id', $report->id)->count();

                if ($existingResponses == 0) {
                    // Create field responses for this report
                    $complianceLevel = $this->getRandomComplianceLevel();

                    // Field 1: Hand hygiene method
                    FieldResponse::create([
                        'daily_report_response_id' => $report->id,
                        'form_field_id' => 1,
                        'field_value' => $complianceLevel['method']
                    ]);
                    $totalResponses++;

                    if ($complianceLevel['method'] !== 'tidak_cuci_tangan') {
                        // Field 2: 5 WHO moments
                        $moments = ['sebelum_kontak_pasien', 'sebelum_prosedur_aseptik', 'setelah_risiko_cairan', 'setelah_kontak_pasien', 'setelah_kontak_area_sekitar'];
                        $selectedMoments = array_slice($moments, 0, $complianceLevel['moments_count']);

                        FieldResponse::create([
                            'daily_report_response_id' => $report->id,
                            'form_field_id' => 2,
                            'field_value' => implode(',', $selectedMoments)
                        ]);
                        $totalResponses++;

                        // Field 3: 6 steps compliance
                        $steps = ['gosok_telapak_tangan', 'gosok_punggung_sela_jari', 'gosok_telapak_sela_jari', 'jari_sisi_dalam_mengunci', 'gosok_ibu_jari_berputar', 'ujung_jari_berputar'];
                        $completedSteps = array_slice($steps, 0, $complianceLevel['steps_count']);

                        FieldResponse::create([
                            'daily_report_response_id' => $report->id,
                            'form_field_id' => 3,
                            'field_value' => implode(',', $completedSteps)
                        ]);
                        $totalResponses++;
                    }

                    $fixed++;
                }
            }
        }

        $this->command->info("✅ Fixed {$fixed} reports with missing field responses");
        $this->command->info("📋 Created {$totalResponses} field responses");
        $this->command->info('🎉 Hand hygiene field responses fix completed!');
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
