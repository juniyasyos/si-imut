<?php

namespace Database\Seeders;

use App\Models\ImutProfile;
use App\Models\FormTemplate;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SampleDailyReportsSeeder extends Seeder
{
    /**
     * Create sample daily reports for multiple ImutProfiles
     */
    public function run(): void
    {
        $this->command->info('🎯 Creating sample daily reports for key ImutProfiles...');

        $unitKerjas = UnitKerja::limit(5)->get();
        $users = User::limit(3)->get();

        if ($unitKerjas->isEmpty() || $users->isEmpty()) {
            $this->command->error('Missing UnitKerja or User data. Please seed them first.');
            return;
        }

        // Get first 20 ImutProfiles that have FormTemplates
        $profilesWithTemplates = ImutProfile::whereHas('formTemplates')
            ->with(['formTemplates', 'imutData'])
            ->limit(20)
            ->get();

        $this->command->info("Found {$profilesWithTemplates->count()} profiles with FormTemplates");

        foreach ($profilesWithTemplates as $profile) {
            $this->createSampleReportsForProfile($profile, $unitKerjas, $users);
        }

        $this->command->info('✅ Sample daily reports created successfully!');
    }

    /**
     * Create sample reports for a specific profile
     */
    private function createSampleReportsForProfile(ImutProfile $profile, $unitKerjas, $users): void
    {
        $formTemplate = $profile->formTemplates->first();

        if (!$formTemplate) {
            return;
        }

        // Skip if already has reports
        $existingReports = DailyReportResponse::where('form_template_id', $formTemplate->id)->count();
        if ($existingReports > 0) {
            $this->command->info("Profile {$profile->id} already has {$existingReports} reports, skipping");
            return;
        }

        $this->command->info("Creating reports for: {$profile->imutData->title} (ID: {$profile->id})");

        // Create reports for last 7 days
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = Carbon::now()->subDays($i)->format('Y-m-d');
        }

        foreach ($dates as $date) {
            $unitKerja = $unitKerjas->random();
            $user = $users->random();

            // Randomize compliance levels
            $complianceLevel = $this->getRandomComplianceLevel();
            $totalScore = $this->calculateScoreByCompliance($complianceLevel);

            $report = DailyReportResponse::create([
                'form_template_id' => $formTemplate->id,
                'unit_kerja_id' => $unitKerja->id,
                'submitted_by' => $user->id,
                'report_date' => $date,
                'total_score' => $totalScore,
                'compliance_status' => $totalScore >= 80 ? 1 : 0,
                'auto_calculated' => true,
                'notes' => $this->generateRandomNotes($complianceLevel),
            ]);

            // Create field responses
            $this->createFieldResponses($report, $formTemplate, $complianceLevel);
        }

        $this->command->info("  ✅ Created 7 reports for profile {$profile->id}");
    }

    /**
     * Get random compliance level
     */
    private function getRandomComplianceLevel(): string
    {
        $levels = ['excellent', 'good', 'fair', 'poor'];
        $weights = [20, 40, 25, 15]; // Weighted towards better compliance

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $levels[$index];
            }
        }

        return 'good'; // fallback
    }

    /**
     * Calculate score based on compliance level
     */
    private function calculateScoreByCompliance(string $level): float
    {
        switch ($level) {
            case 'excellent':
                return rand(95, 100);
            case 'good':
                return rand(80, 94);
            case 'fair':
                return rand(60, 79);
            case 'poor':
                return rand(30, 59);
            default:
                return 75;
        }
    }

    /**
     * Generate random notes
     */
    private function generateRandomNotes(string $level): ?string
    {
        $notes = [
            'excellent' => [
                'Semua prosedur dijalankan dengan sangat baik',
                'Tim menunjukkan komitmen tinggi terhadap standar',
                'Tidak ada kendala berarti dalam pelaksanaan'
            ],
            'good' => [
                'Pelaksanaan sesuai standar dengan beberapa catatan minor',
                'Perlu peningkatan di beberapa aspek',
                'Secara keseluruhan sudah memenuhi target'
            ],
            'fair' => [
                'Ada beberapa area yang perlu perbaikan',
                'Diperlukan monitoring lebih ketat',
                'Sebagian prosedur belum optimal'
            ],
            'poor' => [
                'Banyak aspek yang belum sesuai standar',
                'Perlu tindakan korektif segera',
                'Diperlukan training ulang untuk tim'
            ]
        ];

        $levelNotes = $notes[$level] ?? ['Data normal tanpa catatan khusus'];

        // 70% chance to have notes
        return rand(1, 100) <= 70 ? $levelNotes[array_rand($levelNotes)] : null;
    }

    /**
     * Create field responses for a report
     */
    private function createFieldResponses(DailyReportResponse $report, FormTemplate $formTemplate, string $complianceLevel): void
    {
        $fields = $formTemplate->fields()->with('options')->limit(5)->get(); // First 5 fields

        foreach ($fields as $field) {
            if ($field->options->count() === 0) {
                continue;
            }

            // Choose option based on compliance level
            $option = $this->selectOptionByCompliance($field, $complianceLevel);

            if ($option) {
                FieldResponse::create([
                    'daily_report_response_id' => $report->id,
                    'form_field_id' => $field->id,
                    'field_value' => $option->option_value,
                    'compliance_score' => $option->compliance_value / 100,
                    'is_valid' => true,
                ]);
            }
        }
    }

    /**
     * Select option based on compliance level
     */
    private function selectOptionByCompliance($field, string $level)
    {
        $options = $field->options;

        if ($options->count() === 0) {
            return null;
        }

        // Sort options by compliance value (highest first)
        $sortedOptions = $options->sortByDesc('compliance_value');
        $optionsArray = $sortedOptions->values();

        switch ($level) {
            case 'excellent':
                return $optionsArray->first(); // Best option
            case 'good':
                return $optionsArray->count() > 1 ? $optionsArray[0] : $optionsArray->first();
            case 'fair':
                return $optionsArray->count() > 1 ? $optionsArray[min(1, $optionsArray->count() - 1)] : $optionsArray->first();
            case 'poor':
                return $optionsArray->last(); // Worst option
            default:
                return $optionsArray->random();
        }
    }
}
