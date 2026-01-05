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

        foreach ($template->fields->sortBy('order_index') as $field) {
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
                if ($parent) {
                    $this->command->info("   📋 [Conditional] Hanya muncul jika '{$parent->field_name}' = '{$field->condition_value}'");
                }
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

        // Urutkan fields berdasarkan order_index untuk memastikan parent fields diisi dulu
        $sortedFields = $template->fields->sortBy('order_index');

        // Simulasi response untuk setiap field dengan logika conditional
        foreach ($sortedFields as $field) {
            $response = $this->generateFieldResponse($field, $complianceLevel, $responses);

            // Handle closure responses (untuk field yang dependent on previous responses)
            if (is_callable($response)) {
                $response = $response();
            }

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
            } else {
                $this->command->info("⏭️ {$field->field_name}: [SKIPPED - Conditional field tidak terpenuhi]");
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

    private function generateFieldResponse(EnhancedFormField $field, string $complianceLevel, array $existingResponses = []): mixed
    {
        // Cek conditional logic jika field ini punya parent
        if ($field->parent_field_id) {
            $parentField = EnhancedFormField::find($field->parent_field_id);
            
            if ($parentField) {
                // Cek nilai response dari parent field
                $parentResponse = $existingResponses[$parentField->field_key] ?? null;
                
                // Jika parent belum diisi atau nilai tidak sesuai condition, skip field ini
                if (!$parentResponse || $parentResponse !== $field->condition_value) {
                    $this->command->info("   [Conditional] Parent '{$parentField->field_name}' = '{$parentResponse}', expected '{$field->condition_value}'");
                    return null;
                }
                
                $this->command->info("   [Conditional] ✓ Condition met, parent '{$parentField->field_name}' = '{$parentResponse}'");
            }
        }

        // Generate response berdasarkan field key dan compliance level
        return match ($field->field_key) {
            'data_collector' => 'Dr. Siti Nurhaliza, SpKJ',
            
            'handwashing_compliance' => $complianceLevel,
            
            'total_observations' => match ($complianceLevel) {
                'excellent' => rand(95, 100),
                'good' => rand(70, 85),
                'poor' => rand(30, 50),
                default => rand(50, 75)
            },
            
            'compliant_observations' => function() use ($complianceLevel, $existingResponses) {
                $total = $existingResponses['total_observations'] ?? 100;
                return match ($complianceLevel) {
                    'excellent' => (int)($total * 0.95), // 95%
                    'good' => (int)($total * 0.75),      // 75%
                    'poor' => (int)($total * 0.45),      // 45%
                    default => (int)($total * 0.65)     // 65%
                };
            },
            
            'validation_status' => match ($complianceLevel) {
                'excellent', 'good' => 'validated',
                'poor' => 'needs_review',
                default => 'pending'
            },
            
            'validation_notes' => function() use ($complianceLevel) {
                if (in_array($complianceLevel, ['excellent', 'good'])) {
                    return match ($complianceLevel) {
                        'excellent' => 'Data telah tervalidasi. Compliance sangat baik, pertahankan standar ini.',
                        'good' => 'Data tervalidasi. Compliance baik, ada sedikit area untuk improvement.',
                        default => 'Data tervalidasi dengan catatan.'
                    };
                }
                return null; // Tidak diisi jika compliance poor
            },
            
            'improvement_needed' => $complianceLevel === 'poor' ? 'yes' : 'no',
            
            'improvement_plan' => function() use ($complianceLevel) {
                if ($complianceLevel === 'poor') {
                    return 'Rencana improvement: 1) Pelatihan ulang hand hygiene, 2) Monitoring ketat selama 2 minggu, 3) Evaluasi ulang prosedur';
                }
                return null;
            },
            
            'additional_notes' => function() use ($complianceLevel) {
                return match ($complianceLevel) {
                    'excellent' => 'Tim menunjukkan compliance sangat baik. Berikan apresiasi dan jadikan best practice.',
                    'good' => 'Compliance baik secara keseluruhan. Focus pada area-area yang masih perlu improvement.',
                    'poor' => 'Compliance di bawah standar. Perlu tindakan segera dan monitoring intensif.',
                    default => 'Catatan standar untuk compliance level ini.'
                };
            },
            
            // Field boolean
            'quality_assurance_check' => match ($complianceLevel) {
                'excellent', 'good' => 'true',
                'poor' => 'false',
                default => 'true'
            },
            
            // Field numerik lainnya
            'observation_duration_minutes' => rand(15, 60),
            'number_of_staff_observed' => rand(5, 20),
            
            // Default berdasarkan tipe field
            default => match ($field->field_type) {
                'short_text' => 'Sample response for ' . $field->field_name,
                'long_text' => 'Detailed response for ' . $field->field_name . ' based on compliance level: ' . $complianceLevel,
                'boolean' => rand(0, 1) ? 'true' : 'false',
                'number' => rand(1, 100),
                'select' => function() use ($field) {
                    $options = $field->options()->pluck('option_text')->toArray();
                    return !empty($options) ? $options[array_rand($options)] : 'default_option';
                }(),
                default => 'Generated value for ' . $field->field_key
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
