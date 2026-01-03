<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\UnitKerja;
use App\Models\User;
use App\Models\ImutProfile;
use Carbon\Carbon;

class DailyReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating daily report sample data...');

        // Get existing ImutProfile
        $imutProfile = ImutProfile::first();
        if (!$imutProfile) {
            $this->command->error('No ImutProfile found. Please create one first.');
            return;
        }

        // Create or get form template
        $formTemplate = FormTemplate::where('imut_profile_id', $imutProfile->id)->first();
        if (!$formTemplate) {
            $formTemplate = $this->createSampleFormTemplate($imutProfile);
        }

        // Get users and unit kerjas
        $users = User::limit(5)->get();
        $unitKerjas = UnitKerja::limit(3)->get();

        if ($users->isEmpty() || $unitKerjas->isEmpty()) {
            $this->command->error('No users or unit kerjas found. Please seed them first.');
            return;
        }

        $this->command->info("Creating daily reports for form template: {$formTemplate->title}");

        // Create daily reports for the last 30 days
        for ($i = 30; $i >= 1; $i--) {
            $reportDate = Carbon::now()->subDays($i);

            // Create 1-3 reports per day
            $reportsPerDay = rand(1, 3);

            for ($j = 0; $j < $reportsPerDay; $j++) {
                $user = $users->random();
                $unitKerja = $unitKerjas->random();

                $this->createDailyReport($formTemplate, $user, $unitKerja, $reportDate);
            }
        }

        $totalReports = DailyReportResponse::where('form_template_id', $formTemplate->id)->count();
        $this->command->info("Successfully created {$totalReports} daily reports!");
    }

    private function createSampleFormTemplate(ImutProfile $imutProfile): FormTemplate
    {
        $formTemplate = FormTemplate::create([
            'imut_profile_id' => $imutProfile->id,
            'title' => 'Checklist Kebersihan Tangan Harian',
            'description' => 'Form laporan harian untuk monitoring kebersihan tangan',
            'compliance_method' => 'weighted_average',
            'auto_fail_on_critical' => true,
            'status' => true,
        ]);

        // Create sample fields
        $fields = [
            [
                'field_key' => 'hand_hygiene_available',
                'field_label' => 'Ketersediaan Fasilitas Kebersihan Tangan',
                'field_description' => 'Apakah fasilitas cuci tangan tersedia?',
                'field_type' => 'radio',
                'compliance_weight' => 20,
                'is_critical_field' => true,
                'options' => [
                    ['option_text' => 'Tersedia dan Berfungsi', 'option_value' => 'available', 'is_correct' => true],
                    ['option_text' => 'Tersedia tapi Tidak Berfungsi', 'option_value' => 'not_working', 'is_correct' => false],
                    ['option_text' => 'Tidak Tersedia', 'option_value' => 'not_available', 'is_correct' => false],
                ]
            ],
            [
                'field_key' => 'staff_compliance',
                'field_label' => 'Kepatuhan Staff terhadap Kebersihan Tangan',
                'field_description' => 'Berapa persen staff yang patuh melakukan kebersihan tangan?',
                'field_type' => 'select',
                'compliance_weight' => 30,
                'is_critical_field' => false,
                'options' => [
                    ['option_text' => '90-100%', 'option_value' => '90-100', 'is_correct' => true],
                    ['option_text' => '75-89%', 'option_value' => '75-89', 'is_correct' => true],
                    ['option_text' => '60-74%', 'option_value' => '60-74', 'is_correct' => false],
                    ['option_text' => 'Kurang dari 60%', 'option_value' => '<60', 'is_correct' => false],
                ]
            ],
            [
                'field_key' => 'soap_availability',
                'field_label' => 'Ketersediaan Sabun',
                'field_description' => 'Apakah sabun tersedia di semua wastafel?',
                'field_type' => 'toggle',
                'compliance_weight' => 15,
                'is_critical_field' => true,
                'options' => [
                    ['option_text' => 'Ya', 'option_value' => '1', 'is_correct' => true],
                    ['option_text' => 'Tidak', 'option_value' => '0', 'is_correct' => false],
                ]
            ],
            [
                'field_key' => 'alcohol_based',
                'field_label' => 'Hand Sanitizer Berbasis Alkohol',
                'field_description' => 'Apakah hand sanitizer berbasis alkohol tersedia?',
                'field_type' => 'radio',
                'compliance_weight' => 15,
                'is_critical_field' => false,
                'options' => [
                    ['option_text' => 'Tersedia dan Mudah Diakses', 'option_value' => 'available', 'is_correct' => true],
                    ['option_text' => 'Tersedia tapi Sulit Diakses', 'option_value' => 'limited', 'is_correct' => false],
                    ['option_text' => 'Tidak Tersedia', 'option_value' => 'not_available', 'is_correct' => false],
                ]
            ],
            [
                'field_key' => 'training_compliance',
                'field_label' => 'Pelatihan Kebersihan Tangan',
                'field_description' => 'Apakah staff sudah mendapat pelatihan kebersihan tangan bulan ini?',
                'field_type' => 'checkbox',
                'compliance_weight' => 20,
                'is_critical_field' => false,
                'options' => [
                    ['option_text' => 'Dokter', 'option_value' => 'doctor', 'is_correct' => true],
                    ['option_text' => 'Perawat', 'option_value' => 'nurse', 'is_correct' => true],
                    ['option_text' => 'Staff Farmasi', 'option_value' => 'pharmacy', 'is_correct' => true],
                    ['option_text' => 'Staff Laboratorium', 'option_value' => 'lab', 'is_correct' => true],
                ]
            ]
        ];

        foreach ($fields as $index => $fieldData) {
            $field = EnhancedFormField::create([
                'form_template_id' => $formTemplate->id,
                'field_key' => $fieldData['field_key'],
                'field_label' => $fieldData['field_label'],
                'field_description' => $fieldData['field_description'],
                'field_type' => $fieldData['field_type'],
                'compliance_weight' => $fieldData['compliance_weight'],
                'is_critical_field' => $fieldData['is_critical_field'],
                'order_index' => $index + 1,
                'compliance_rules' => json_encode(['required' => true]),
            ]);

            foreach ($fieldData['options'] as $optionData) {
                FormFieldOption::create([
                    'enhanced_form_field_id' => $field->id,
                    'option_text' => $optionData['option_text'],
                    'option_value' => $optionData['option_value'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }
        }

        return $formTemplate;
    }

    private function createDailyReport(FormTemplate $formTemplate, User $user, UnitKerja $unitKerja, Carbon $reportDate): void
    {
        // Create the daily report response
        $dailyReport = DailyReportResponse::create([
            'form_template_id' => $formTemplate->id,
            'unit_kerja_id' => $unitKerja->id,
            'submitted_by' => $user->id,
            'report_date' => $reportDate,
            'total_score' => 0, // Will be calculated
            'compliance_status' => false, // Will be calculated
            'auto_calculated' => true,
            'notes' => $this->getRandomNotes(),
            'created_at' => $reportDate->copy()->addHours(rand(8, 16))->addMinutes(rand(0, 59)),
        ]);

        // Create field responses
        $totalScore = 0;
        $totalWeight = 0;
        $criticalFieldsFailed = false;

        foreach ($formTemplate->formFields as $field) {
            $response = $this->generateRandomResponse($field);
            $complianceScore = $this->calculateFieldCompliance($field, $response);

            FieldResponse::create([
                'daily_report_response_id' => $dailyReport->id,
                'form_field_id' => $field->id,
                'field_value' => $response, // Will be auto-cast to JSON by the model
                'compliance_score' => $complianceScore,
                'is_valid' => true,
                'validation_message' => rand(1, 10) > 7 ? $this->getRandomFieldNote() : null,
            ]);

            // Calculate weighted score
            $totalScore += $complianceScore * $field->compliance_weight;
            $totalWeight += $field->compliance_weight;

            // Check critical fields
            if ($field->is_critical_field && $complianceScore < 1.0) {
                $criticalFieldsFailed = true;
            }
        }

        // Calculate final score and compliance status
        $finalScore = $totalWeight > 0 ? ($totalScore / $totalWeight) * 100 : 0;
        $complianceStatus = $finalScore >= 80 && !$criticalFieldsFailed;

        // Update the daily report
        $dailyReport->update([
            'total_score' => $finalScore,
            'compliance_status' => $complianceStatus,
            'calculation_details' => [
                'method' => 'weighted_average',
                'total_weighted_score' => $totalScore,
                'total_weight' => $totalWeight,
                'final_percentage' => $finalScore,
                'critical_fields_failed' => $criticalFieldsFailed,
                'threshold' => 80,
            ]
        ]);
    }

    private function generateRandomResponse(EnhancedFormField $field): array|string
    {
        $options = $field->options;

        if ($options->isEmpty()) {
            return 'sample_response';
        }

        // Bias towards correct answers (70% chance)
        $correctOptions = $options->where('is_correct', true);
        $incorrectOptions = $options->where('is_correct', false);

        if (rand(1, 10) <= 7 && $correctOptions->isNotEmpty()) {
            $chosen = $correctOptions->random();
        } else {
            $chosen = $incorrectOptions->isNotEmpty() ? $incorrectOptions->random() : $options->random();
        }

        // Handle different field types
        switch ($field->field_type) {
            case 'checkbox':
                // For checkbox, sometimes select multiple options
                if (rand(1, 10) > 6) {
                    $selected = $options->random(min(rand(2, 3), $options->count()));
                    return $selected->pluck('option_value')->toArray();
                }
                return [$chosen->option_value];

            case 'toggle':
                return rand(1, 10) > 3 ? '1' : '0';

            default:
                return $chosen->option_value;
        }
    }

    private function calculateFieldCompliance(EnhancedFormField $field, array|string $response): float
    {
        // Handle array responses (checkbox)
        if (is_array($response)) {
            $correctCount = 0;
            $totalResponses = count($response);

            foreach ($response as $value) {
                $option = $field->options->where('option_value', $value)->first();
                if ($option && $option->is_correct) {
                    $correctCount++;
                }
            }

            return $totalResponses > 0 ? $correctCount / $totalResponses : 0;
        }

        // Handle single responses
        $option = $field->options->where('option_value', $response)->first();
        return $option && $option->is_correct ? 1.0 : 0.0;
    }

    private function getRandomNotes(): string
    {
        $notes = [
            'Kondisi normal, tidak ada kendala.',
            'Beberapa staff perlu reminder untuk kebersihan tangan.',
            'Sabun hampir habis, perlu restocking.',
            'Hand sanitizer perlu diisi ulang.',
            'Staff sudah menunjukkan kepatuhan yang baik.',
            'Ditemukan beberapa wastafel yang tidak berfungsi.',
            'Pelatihan tambahan mungkin diperlukan.',
            'Kondisi fasilitas dalam keadaan baik.',
        ];

        return rand(1, 10) > 3 ? $notes[array_rand($notes)] : '';
    }

    private function getRandomFieldNote(): string
    {
        $notes = [
            'Perlu perbaikan segera.',
            'Staff sudah diberikan reminder.',
            'Akan ditindaklanjuti minggu depan.',
            'Kondisi sudah membaik dari kemarin.',
            'Masih perlu monitoring lebih ketat.',
        ];

        return $notes[array_rand($notes)];
    }
}
