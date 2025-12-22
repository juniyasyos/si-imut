<?php

namespace Database\Seeders;

use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HandwashingSimulationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 SIMULASI FORM CUCI TANGAN');
        $this->command->info('============================');

        // Ambil template cuci tangan
        $template = FormTemplate::where('title', 'LIKE', '%Cuci Tangan%')->latest()->first();
        if (!$template) {
            $this->command->error('Template cuci tangan tidak ditemukan!');
            return;
        }

        $this->command->info("📋 Template: {$template->title}");
        $this->command->info("Total fields: " . $template->fields()->count());
        $this->command->info('');

        // Tampilkan struktur form
        $this->showFormStructure($template);

        // Simulasi 3 scenario berbeda
        $scenarios = [
            ['name' => 'Compliance Sangat Baik', 'compliance' => 'excellent'],
            ['name' => 'Compliance Baik', 'compliance' => 'good'],
            ['name' => 'Compliance Kurang', 'compliance' => 'poor']
        ];

        foreach ($scenarios as $index => $scenario) {
            $this->command->info("🎯 SKENARIO " . ($index + 1) . ": " . $scenario['name']);
            $this->command->info('----------------------------------------');
            $this->simulateFormSubmission($template, $scenario['compliance']);
            $this->command->info('');
        }

        $this->command->info('✅ Simulasi selesai!');
    }

    private function showFormStructure(FormTemplate $template): void
    {
        $this->command->info('📝 STRUKTUR FORM:');

        foreach ($template->fields as $field) {
            $critical = $field->is_critical_field ? ' [CRITICAL]' : '';
            $weight = $field->compliance_weight > 0 ? " (Weight: {$field->compliance_weight})" : '';

            $this->command->info("{$field->order_index}. {$field->field_name} ({$field->field_type}){$critical}{$weight}");

            if ($field->options->count() > 0) {
                foreach ($field->options as $option) {
                    $compliance = match ($option->compliance_value) {
                        2 => '🟢 Excellent',
                        1 => '🟡 Pass',
                        0 => '🔴 Fail',
                        default => '⚪ N/A'
                    };
                    $this->command->info("   - {$option->option_text} → {$compliance}");
                }
            }

            if ($field->parent_field_id) {
                $parent = EnhancedFormField::find($field->parent_field_id);
                $this->command->info("   [Conditional: Shows when '{$parent->field_name}' = '{$field->condition_value}']");
            }
        }
        $this->command->info('');
    }

    private function simulateFormSubmission(FormTemplate $template, string $complianceLevel): void
    {
        // Get atau buat unit kerja dan user untuk simulasi
        $unitKerja = UnitKerja::first();
        $user = User::first();

        if (!$unitKerja || !$user) {
            $this->command->warn('Unit Kerja atau User tidak ditemukan, skip simulasi');
            return;
        }

        $formTemplate = \App\Models\FormTemplate::first();
        $formTemplateId = $formTemplate->id ?? null;

        // Create daily report response; always include form_template_id and optionally 
        $dailyReport = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unitKerja->id,
            'submitted_by' => $user->id,
            'report_date' => Carbon::today(),
            'total_score' => 0,
            'compliance_status' => 'pending',
            'auto_calculated' => true,
        ]);

        $responses = [];
        $calculationDetails = [];

        // Simulasi response untuk setiap field
        foreach ($template->fields as $field) {
            $response = $this->generateFieldResponse($field, $complianceLevel);

            if ($response !== null) {
                $responses[$field->field_key] = $response;

                // Create field response record
                FieldResponse::create([
                    'daily_report_response_id' => $dailyReport->id,
                    'enhanced_form_field_id' => $field->id,
                    'response_value' => is_array($response) ? json_encode($response) : $response,
                    'field_score' => $field->calculateFieldScore($response),
                ]);

                $this->command->info("✏️ {$field->field_name}: {$response}");
                if ($field->compliance_weight > 0) {
                    $score = $field->calculateFieldScore($response);
                    $this->command->info("   Score: {$score} (Weight: {$field->compliance_weight})");
                }
            }
        }

        // Calculate final compliance
        $complianceResult = $template->calculateCompliance($responses);

        // Update daily report with results
        $dailyReport->update([
            'total_score' => $complianceResult['total_score'],
            'compliance_status' => $complianceResult['compliance_status'],
            'calculation_details' => $complianceResult,
        ]);

        // Show results
        $this->showResults($complianceResult, $dailyReport);
    }

    private function generateFieldResponse(EnhancedFormField $field, string $complianceLevel): mixed
    {
        // Skip conditional fields yang tidak trigger
        if ($field->parent_field_id && $complianceLevel !== $field->condition_value) {
            return null;
        }

        return match ($field->field_key) {
            'data_collector' => 'Dr. Siti Nurhaliza, SpKJ',
            'handwashing_compliance' => $complianceLevel,
            'total_observations' => match ($complianceLevel) {
                'excellent' => 100,
                'good' => 85,
                'poor' => 50,
                default => 75
            },
            'validation_status' => 'true',
            'additional_notes' => $complianceLevel === 'poor'
                ? 'Perlu pelatihan ulang dan monitoring ketat untuk minggu depan'
                : null,
            default => match ($field->field_type) {
                'short_text' => 'Sample text',
                'boolean' => 'true',
                'number' => rand(1, 100),
                default => 'default_value'
            }
        };
    }

    private function showResults(array $complianceResult, DailyReportResponse $dailyReport): void
    {
        $this->command->info('📊 HASIL COMPLIANCE:');
        $details = $complianceResult['calculation_details'];
        $this->command->info("Score: {$details['raw_score']}/{$details['max_score']} = {$complianceResult['total_score']}%");

        $statusColor = $complianceResult['compliance_status'] ? '🟢' : '🔴';
        $statusText = $complianceResult['compliance_status'] ? 'COMPLIANT' : 'NON-COMPLIANT';

        $this->command->info("Status: {$statusColor} {$statusText}");

        if ($complianceResult['critical_failed']) {
            $this->command->warn('⚠️  CRITICAL FIELD FAILED - Auto Non-Compliant');
        }

        $this->command->info('');
        $this->command->info("💾 Data tersimpan dengan ID: {$dailyReport->id}");
        $this->command->info("📅 Report Date: {$dailyReport->report_date}");
        $unitName = $dailyReport->unitKerja->name ?? 'N/A';
        $this->command->info("🏥 Unit Kerja: {$unitName}");
    }
}
