<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class AllConfiguredProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating comprehensive data for ALL configured profiles...');

        // Get all profiles that should have JSON configurations
        $configuredProfiles = $this->getConfiguredProfiles();

        $users = User::limit(10)->get();

        if ($users->isEmpty()) {
            $this->command->error('❌ No users found. Please seed users first.');
            return;
        }

        $totalReports = 0;
        $totalResponses = 0;

        foreach ($configuredProfiles as $profileType => $profiles) {
            $this->command->info("📊 Processing {$profileType} profiles ({$profiles->count()} profiles)...");

            foreach ($profiles as $profile) {
                $template = FormTemplate::where('imut_profile_id', $profile->id)->first();
                if (!$template) {
                    $this->command->warn("⚠️  No FormTemplate found for profile {$profile->version}");
                    continue;
                }

                // Skip if already has data
                if (DailyReportResponse::where('form_template_id', $template->id)->exists()) {
                    continue;
                }

                // Create 7 days of historical data
                for ($day = 6; $day >= 0; $day--) {
                    $reportDate = Carbon::now()->subDays($day);
                    $user = $users->random();

                    $dailyReport = DailyReportResponse::create([
                        'form_template_id' => $template->id,
                        'unit_kerja_id' => 1, // Default unit kerja
                        'submitted_by' => $user->id,
                        'report_date' => $reportDate->toDateString(),
                        'total_score' => rand(75, 98),
                        'compliance_status' => rand(0, 1),
                        'auto_calculated' => true,
                        'responses' => json_encode([]),
                        'notes' => "Sample data for {$profileType} - {$profile->version}",
                        'calculation_details' => json_encode([])
                    ]);

                    // Create field responses based on profile type
                    $fieldResponses = $this->createFieldResponsesForType($profileType, $dailyReport->id);
                    $totalResponses += count($fieldResponses);
                    $totalReports++;
                }

                $this->command->info("✅ Created 7 days data for {$profile->version}");
            }
        }

        $this->command->info('🎉 Summary:');
        $this->command->info("📈 Total Daily Reports: {$totalReports}");
        $this->command->info("📋 Total Field Responses: {$totalResponses}");
        $this->command->info('✅ All configured profiles data seeding completed!');
    }

    /**
     * Get all profiles that have JSON configurations
     */
    private function getConfiguredProfiles(): array
    {
        return [
            'kepatuhan-kebersihan-tangan' => ImutProfile::whereHas('imutData', function ($q) {
                $q->where('title', 'like', '%kebersihan tangan%');
            })->get(),

            'kepatuhan-penggunaan-apd' => ImutProfile::whereHas('imutData', function ($q) {
                $q->where('title', 'like', '%penggunaan apd%')
                    ->orWhere('title', 'like', '%alat pelindung%');
            })->get(),

            'ketepatan-identifikasi-pasien' => ImutProfile::whereHas('imutData', function ($q) {
                $q->where('title', 'like', '%identifikasi pasien%');
            })->get(),

            'pencegahan-risiko-jatuh' => ImutProfile::whereHas('imutData', function ($q) {
                $q->where('title', 'like', '%risiko jatuh%')
                    ->orWhere('title', 'like', '%pencegahan%jatuh%');
            })->get(),
        ];
    }

    /**
     * Create field responses based on profile type
     */
    private function createFieldResponsesForType(string $profileType, int $dailyReportId): array
    {
        $responses = [];

        switch ($profileType) {
            case 'kepatuhan-kebersihan-tangan':
                $responses = $this->createHandHygieneResponses($dailyReportId);
                break;

            case 'kepatuhan-penggunaan-apd':
                $responses = $this->createAPDResponses($dailyReportId);
                break;

            case 'ketepatan-identifikasi-pasien':
                $responses = $this->createPatientIdResponses($dailyReportId);
                break;

            case 'pencegahan-risiko-jatuh':
                $responses = $this->createFallPreventionResponses($dailyReportId);
                break;
        }

        return $responses;
    }

    /**
     * Create hand hygiene field responses
     */
    private function createHandHygieneResponses(int $dailyReportId): array
    {
        $responses = [];
        $complianceLevel = $this->getRandomHandHygieneLevel();

        // Field 1: Hand hygiene method
        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 1,
            'field_value' => $complianceLevel['method']
        ]);

        if ($complianceLevel['method'] !== 'tidak_cuci_tangan') {
            // Field 2: 5 WHO moments
            $moments = ['sebelum_kontak_pasien', 'sebelum_prosedur_aseptik', 'setelah_risiko_cairan', 'setelah_kontak_pasien', 'setelah_kontak_area_sekitar'];
            $selectedMoments = array_slice($moments, 0, $complianceLevel['moments_count']);

            $responses[] = FieldResponse::create([
                'daily_report_response_id' => $dailyReportId,
                'form_field_id' => 2,
                'field_value' => implode(',', $selectedMoments)
            ]);

            // Field 3: 6 steps compliance
            $steps = ['gosok_telapak_tangan', 'gosok_punggung_sela_jari', 'gosok_telapak_sela_jari', 'jari_sisi_dalam_mengunci', 'gosok_ibu_jari_berputar', 'ujung_jari_berputar'];
            $completedSteps = array_slice($steps, 0, $complianceLevel['steps_count']);

            $responses[] = FieldResponse::create([
                'daily_report_response_id' => $dailyReportId,
                'form_field_id' => 3,
                'field_value' => implode(',', $completedSteps)
            ]);
        }

        return $responses;
    }

    /**
     * Create APD field responses
     */
    private function createAPDResponses(int $dailyReportId): array
    {
        $responses = [];

        $apdLevels = ['lengkap', 'kurang_lengkap', 'tidak_lengkap'];
        $usageLevels = ['benar', 'sedikit_salah', 'banyak_salah', 'salah'];
        $disposalLevels = ['ya', 'tidak', 'na'];

        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 1,
            'field_value' => $apdLevels[array_rand($apdLevels)]
        ]);

        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 2,
            'field_value' => $usageLevels[array_rand($usageLevels)]
        ]);

        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 3,
            'field_value' => $disposalLevels[array_rand($disposalLevels)]
        ]);

        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 4,
            'field_value' => 'Observasi penggunaan APD sesuai SOP'
        ]);

        return $responses;
    }

    /**
     * Create patient identification field responses
     */
    private function createPatientIdResponses(int $dailyReportId): array
    {
        $responses = [];

        $identificationMethods = ['dua_identitas', 'satu_identitas', 'tidak_identifikasi'];
        $selectedMethod = $identificationMethods[array_rand($identificationMethods)];

        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 1,
            'field_value' => $selectedMethod
        ]);

        if ($selectedMethod !== 'tidak_identifikasi') {
            $timings = ['sebelum_obat', 'sebelum_tindakan', 'sebelum_sampel', 'pergantian_shift'];
            $selectedTimings = array_slice($timings, 0, rand(2, 3));

            $responses[] = FieldResponse::create([
                'daily_report_response_id' => $dailyReportId,
                'form_field_id' => 2,
                'field_value' => implode(',', $selectedTimings)
            ]);
        }

        $braceletStatus = ['benar', 'data_salah', 'tidak_terpasang'];
        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 3,
            'field_value' => $braceletStatus[array_rand($braceletStatus)]
        ]);

        return $responses;
    }

    /**
     * Create fall prevention field responses
     */
    private function createFallPreventionResponses(int $dailyReportId): array
    {
        $responses = [];

        $assessmentLevels = ['semua_dinilai', 'sebagian_dinilai', 'tidak_ada_asesmen'];
        $selectedAssessment = $assessmentLevels[array_rand($assessmentLevels)];

        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 1,
            'field_value' => $selectedAssessment
        ]);

        if ($selectedAssessment !== 'tidak_ada_asesmen') {
            $interventions = ['bed_rail', 'gelang_risiko', 'edukasi', 'call_bell', 'lantai_aman', 'pencahayaan'];
            $selectedInterventions = array_slice($interventions, 0, rand(3, 6));

            $responses[] = FieldResponse::create([
                'daily_report_response_id' => $dailyReportId,
                'form_field_id' => 2,
                'field_value' => implode(',', $selectedInterventions)
            ]);
        }

        $documentationLevels = ['lengkap', 'kurang_lengkap', 'tidak_ada'];
        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 3,
            'field_value' => $documentationLevels[array_rand($documentationLevels)]
        ]);

        return $responses;
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
    private function getRandomHandHygieneLevel(): array
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
