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

class CompleteFormTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Complete FormTemplate and Data Population...');

        // Step 1: Ensure all profiles have proper FormTemplates with JSON configs
        $this->ensureFormTemplatesWithConfig();

        // Step 2: Populate sample data for all profiles
        $this->populateComprehensiveData();

        $this->command->info('✅ Complete FormTemplate and Data Population finished!');
    }

    /**
     * Ensure all ImutProfiles have FormTemplates with proper JSON configurations
     */
    private function ensureFormTemplatesWithConfig(): void
    {
        $this->command->info('📋 Ensuring FormTemplates with JSON configurations...');

        $profiles = ImutProfile::with('imutData')->get();
        $created = 0;
        $updated = 0;

        foreach ($profiles as $profile) {
            $imutDataTitle = strtolower($profile->imutData->title ?? '');
            $configData = $this->loadFormConfiguration($imutDataTitle);

            if (!$configData) {
                $this->command->warn("⚠️  No configuration found for: {$imutDataTitle}");
                continue;
            }

            // Check if FormTemplate exists
            $formTemplate = FormTemplate::where('imut_profile_id', $profile->id)->first();

            if ($formTemplate) {
                // Update existing template with proper config
                $formTemplate->update([
                    'scoring_config' => json_encode(['form_fields' => $configData['form_fields']])
                ]);
                $updated++;
            } else {
                // Create new FormTemplate
                $template = $configData['form_template'];
                FormTemplate::create([
                    'imut_profile_id' => $profile->id,
                    'title' => $template['title'] . ' - ' . $profile->version,
                    'description' => $template['description'],
                    'compliance_method' => $template['compliance_method'],
                    'auto_fail_on_critical' => $template['auto_fail_on_critical'],
                    'scoring_config' => json_encode(['form_fields' => $configData['form_fields']])
                ]);
                $created++;
            }
        }

        $this->command->info("✅ FormTemplates created: {$created}");
        $this->command->info("🔄 FormTemplates updated: {$updated}");
    }

    /**
     * Load form configuration from JSON file
     */
    private function loadFormConfiguration(string $imutDataTitle): ?array
    {
        $configFile = null;

        if ($this->containsKeywords($imutDataTitle, ['cuci tangan', 'hand hygiene', 'kebersihan tangan'])) {
            $configFile = 'kepatuhan-kebersihan-tangan.json';
        } elseif ($this->containsKeywords($imutDataTitle, ['apd', 'alat pelindung', 'protective equipment'])) {
            $configFile = 'kepatuhan-penggunaan-apd.json';
        } elseif ($this->containsKeywords($imutDataTitle, ['identifikasi pasien', 'patient identification'])) {
            $configFile = 'ketepatan-identifikasi-pasien.json';
        } elseif ($this->containsKeywords($imutDataTitle, ['jatuh', 'fall', 'risiko jatuh'])) {
            $configFile = 'pencegahan-risiko-jatuh.json';
        }

        if (!$configFile) {
            return null;
        }

        $configPath = database_path('data/form-configurations/' . $configFile);

        if (!File::exists($configPath)) {
            return null;
        }

        $jsonContent = File::get($configPath);
        return json_decode($jsonContent, true);
    }

    /**
     * Check if title contains keywords
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
     * Populate comprehensive data for all FormTemplates
     */
    private function populateComprehensiveData(): void
    {
        $this->command->info('📊 Populating comprehensive sample data...');

        $formTemplates = FormTemplate::with(['imutProfile.imutData'])->get();
        $users = User::limit(10)->get();

        if ($users->isEmpty()) {
            $this->command->error('❌ No users found. Please seed users first.');
            return;
        }

        $totalReports = 0;
        $totalResponses = 0;

        foreach ($formTemplates as $template) {
            $imutDataTitle = strtolower($template->imutProfile->imutData->title ?? '');

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
                    'submitted_by' => $user->id,
                    'submission_date' => $reportDate,
                    'compliance_status' => $this->getRandomComplianceStatus(),
                    'overall_score' => rand(75, 98),
                    'additional_notes' => "Data sample untuk {$template->imutProfile->imutData->title}",
                    'is_validated' => true,
                    'validated_by' => $user->id,
                    'validated_at' => $reportDate->addHours(2)
                ]);

                // Create field responses based on form configuration
                $fieldResponses = $this->createFieldResponsesForFormType($imutDataTitle, $dailyReport->id);
                $totalResponses += count($fieldResponses);
                $totalReports++;
            }

            $this->command->info("✅ Created data for {$template->imutProfile->imutData->title}");
        }

        $this->command->info("📈 Total Daily Reports created: {$totalReports}");
        $this->command->info("📋 Total Field Responses created: {$totalResponses}");
    }

    /**
     * Create field responses based on form type
     */
    private function createFieldResponsesForFormType(string $formType, int $dailyReportId): array
    {
        $responses = [];

        if ($this->containsKeywords($formType, ['cuci tangan', 'hand hygiene', 'kebersihan tangan'])) {
            $responses = $this->createHandHygieneResponses($dailyReportId);
        } elseif ($this->containsKeywords($formType, ['apd', 'alat pelindung'])) {
            $responses = $this->createAPDResponses($dailyReportId);
        } elseif ($this->containsKeywords($formType, ['identifikasi pasien'])) {
            $responses = $this->createPatientIdResponses($dailyReportId);
        } elseif ($this->containsKeywords($formType, ['jatuh', 'risiko jatuh'])) {
            $responses = $this->createFallPreventionResponses($dailyReportId);
        }

        return $responses;
    }

    /**
     * Create hand hygiene field responses
     */
    private function createHandHygieneResponses(int $dailyReportId): array
    {
        $responses = [];
        $complianceLevel = $this->getRandomComplianceLevel();

        // Hand hygiene method
        $responses[] = FieldResponse::create([
            'daily_report_response_id' => $dailyReportId,
            'form_field_id' => 1,
            'field_value' => $complianceLevel['method']
        ]);

        // 5 WHO moments (if method is compliant)
        if ($complianceLevel['method'] !== 'tidak_cuci_tangan') {
            $moments = ['sebelum_kontak_pasien', 'sebelum_prosedur_aseptik', 'setelah_risiko_cairan', 'setelah_kontak_pasien'];
            $selectedMoments = array_slice($moments, 0, rand(2, 4));

            $responses[] = FieldResponse::create([
                'daily_report_response_id' => $dailyReportId,
                'form_field_id' => 2,
                'field_value' => implode(',', $selectedMoments)
            ]);

            // 6 steps compliance
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
        $complianceLevel = $this->getRandomComplianceStatus();

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
    private function getRandomComplianceLevel(): array
    {
        $levels = [
            ['method' => 'hand_rub', 'steps_count' => 6],
            ['method' => 'air_sabun', 'steps_count' => 5],
            ['method' => 'hand_rub', 'steps_count' => 4],
            ['method' => 'tidak_cuci_tangan', 'steps_count' => 0]
        ];

        $weights = [40, 30, 20, 10]; // Higher chance for good compliance

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
