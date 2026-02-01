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
        $this->command->info('🧪 SIMULASI FORM Kepatuhan Kebersihan Tangan - Unit IGD');
        $this->command->info('========================================================');

        // Ambil template Kepatuhan Kebersihan Tangan
        $template = FormTemplate::where('title', 'LIKE', '%Kepatuhan Kebersihan Tangan%')->latest()->first();
        if (!$template) {
            $this->command->error('Template Kepatuhan Kebersihan Tangan tidak ditemukan!');
            return;
        }

        $this->command->info("📋 Template: {$template->title}");
        $this->command->info("Total fields: " . $template->fields()->count());
        $this->command->info('');

        // Tampilkan struktur form
        $this->showFormStructure($template);

        // Generate data untuk 20 hari ke belakang dari now()
        $startDate = now()->subDays(20);
        $endDate = now();

        $this->command->info("📅 Generating data from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->command->info("📊 Target: ~8 entries per day");
        $this->command->newLine();

        $totalGenerated = 0;

        // Loop untuk setiap hari
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Generate 6-10 entries per day (rata-rata 8)
            $entriesPerDay = rand(6, 10);

            $this->command->info("📆 {$date->format('Y-m-d')} - Generating {$entriesPerDay} entries");

            for ($i = 0; $i < $entriesPerDay; $i++) {
                // Variasi compliance level
                $complianceLevel = $this->getRandomComplianceLevel();
                $staffName = $this->getRandomStaffName($i);

                $this->command->info("  └─ Entry " . ($i + 1) . ": {$staffName} ({$complianceLevel})");
                $this->simulateFormSubmission($template, $complianceLevel, $date->format('Y-m-d'));

                $totalGenerated++;
            }

            $this->command->newLine();
        }

        $this->command->info("✅ Simulasi selesai! Total {$totalGenerated} entries generated.");
    }

    private function getRandomComplianceLevel(): string
    {
        $levels = [
            'excellent' => 40, // 40% excellent
            'good' => 35,      // 35% good
            'poor' => 20,      // 20% poor
            'very_poor' => 5   // 5% very poor
        ];

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($levels as $level => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $level;
            }
        }

        return 'good';
    }

    private function getRandomStaffName(int $index): string
    {
        $staffTypes = [
            'Perawat IGD Shift Pagi',
            'Dokter Jaga IGD',
            'Perawat IGD Shift Malam',
            'Tim Resusitasi IGD',
            'Perawat Triase IGD',
            'Cleaning Service IGD',
            'Dokter Spesialis IGD',
            'Perawat IGD Ruang Observasi',
            'Mahasiswa Praktek IGD',
            'Supervisor IGD',
            'Perawat Senior IGD',
            'Administrasi IGD',
            'Radiografer IGD'
        ];

        return $staffTypes[$index % count($staffTypes)];
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

    private function simulateFormSubmission(FormTemplate $template, string $complianceLevel, string $date = null): void
    {
        // Pastikan unit kerja adalah IGD
        $unitKerja = UnitKerja::where('unit_name', 'LIKE', '%IGD%')
            ->orWhere('unit_name', 'LIKE', '%Emergency%')
            ->orWhere('unit_name', 'LIKE', '%Gawat Darurat%')
            ->first();

        if (!$unitKerja) {
            // Jika tidak ada unit IGD, ambil unit kerja pertama
            $unitKerja = UnitKerja::first();
        }

        $user = User::first();

        if (!$unitKerja || !$user) {
            $this->command->warn('Unit Kerja atau User tidak ditemukan, skip simulasi');
            return;
        }

        // Create daily report response
        $dailyReport = DailyReportResponse::create([
            'form_template_id' => $template->id,
            'unit_kerja_id' => $unitKerja->id,
            'submitted_by' => $user->id,
            'report_date' => $date ? Carbon::parse($date) : Carbon::today(),
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
                    'form_field_id' => $field->id,
                    'field_value' => is_array($response) ? $response : [$response],
                    'compliance_score' => $field->calculateFieldScore($response) ?? 0,
                ]);

                $displayValue = $this->getDisplayValue($field, $response);
                $this->command->info("✏️ {$field->field_label}: {$displayValue}");
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
            // Form Kepatuhan Kebersihan Tangan
            'hand_hygiene_method' => match ($complianceLevel) {
                'excellent' => 'hand_rub',
                'good' => rand(0, 1) ? 'hand_rub' : 'air_sabun',
                'poor' => rand(0, 2) ? 'air_sabun' : 'hand_rub',
                'very_poor' => 'tidak_cuci_tangan',
                default => 'hand_rub'
            },

            'hand_hygiene_indication' => match ($complianceLevel) {
                'excellent' => [
                    'sebelum_kontak_pasien',
                    'sebelum_prosedur_aseptik',
                    'setelah_risiko_cairan',
                    'setelah_kontak_pasien',
                    'setelah_kontak_area_sekitar'
                ],
                'good' => array_slice([
                    'sebelum_kontak_pasien',
                    'sebelum_prosedur_aseptik',
                    'setelah_risiko_cairan',
                    'setelah_kontak_pasien',
                    'setelah_kontak_area_sekitar'
                ], 0, rand(3, 4)),
                'poor' => array_slice([
                    'sebelum_kontak_pasien',
                    'sebelum_prosedur_aseptik',
                    'setelah_risiko_cairan'
                ], 0, rand(1, 2)),
                'very_poor' => [
                    'sebelum_kontak_pasien'
                ],
                default => [
                    'sebelum_kontak_pasien',
                    'sebelum_prosedur_aseptik'
                ]
            },

            'six_steps_compliance' => match ($complianceLevel) {
                'excellent' => [
                    'gosok_telapak_tangan',
                    'gosok_punggung_sela_jari',
                    'gosok_telapak_sela_jari',
                    'jari_sisi_dalam_mengunci',
                    'gosok_ibu_jari_berputar',
                    'ujung_jari_berputar'
                ],
                'good' => array_slice([
                    'gosok_telapak_tangan',
                    'gosok_punggung_sela_jari',
                    'gosok_telapak_sela_jari',
                    'jari_sisi_dalam_mengunci',
                    'gosok_ibu_jari_berputar',
                    'ujung_jari_berputar'
                ], 0, rand(4, 5)),
                'poor' => array_slice([
                    'gosok_telapak_tangan',
                    'gosok_punggung_sela_jari',
                    'gosok_telapak_sela_jari',
                    'jari_sisi_dalam_mengunci'
                ], 0, rand(2, 3)),
                'very_poor' => [
                    'gosok_telapak_tangan'
                ],
                default => [
                    'gosok_telapak_tangan',
                    'gosok_punggung_sela_jari',
                    'gosok_telapak_sela_jari'
                ]
            },

            // Legacy fields untuk form lain
            'data_collector' => 'Dr. Siti Nurhaliza, SpKJ',

            'handwashing_compliance' => $complianceLevel,

            'total_observations' => match ($complianceLevel) {
                'excellent' => rand(95, 100),
                'good' => rand(70, 85),
                'poor' => rand(30, 50),
                default => rand(50, 75)
            },

            'compliant_observations' => function () use ($complianceLevel, $existingResponses) {
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

            'validation_notes' => function () use ($complianceLevel) {
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

            'improvement_plan' => function () use ($complianceLevel) {
                if ($complianceLevel === 'poor') {
                    return 'Rencana improvement: 1) Pelatihan ulang hand hygiene, 2) Monitoring ketat selama 2 minggu, 3) Evaluasi ulang prosedur';
                }
                return null;
            },

            'additional_notes' => function () use ($complianceLevel) {
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
                'select' => $this->getRandomOptionFromField($field),
                default => 'Generated value for ' . $field->field_key
            }
        };
    }

    private function getDisplayValue(EnhancedFormField $field, $response): string
    {
        if (is_array($response)) {
            $displayValues = [];
            foreach ($response as $value) {
                $option = $field->options()->where('option_value', $value)->first();
                $displayValues[] = $option ? $option->option_text : $value;
            }
            return implode(', ', $displayValues);
        } else {
            $option = $field->options()->where('option_value', $response)->first();
            return $option ? $option->option_text : $response;
        }
    }

    private function getRandomOptionFromField(EnhancedFormField $field): string
    {
        $options = $field->options()->pluck('option_text')->toArray();
        return !empty($options) ? $options[array_rand($options)] : 'default_option';
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
        $unitName = $dailyReport->unitKerja->unit_name ?? 'N/A';
        $this->command->info("🏥 Unit Kerja: {$unitName}");
    }
}
