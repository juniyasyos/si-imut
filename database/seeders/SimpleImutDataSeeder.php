<?php

namespace Database\Seeders;

use App\Models\ImutData;
use App\Models\ImutProfile;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SimpleImutDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting SimpleImutDataSeeder...');

        $indicators = $this->getUniqueIndicators();
        $this->command->info('📊 Creating ' . count($indicators) . ' indicators');

        foreach ($indicators as $indicator) {
            $this->command->info("📈 Creating data for: {$indicator['title']}");

            // Create ImutData with correct column names
            $imutData = ImutData::create([
                'title' => $indicator['title'],
                'imut_kategori_id' => 1, // Default category
                'description' => 'Generated indicator for IMUT system',
                'status' => true,
                'created_by' => 1
            ]);

            // Generate quarters from current year forward (2026, 2027, 2028)
            $this->generateQuarters($imutData, $indicator);
        }

        $this->command->info('✅ SimpleImutDataSeeder completed successfully!');
    }

    private function generateQuarters(ImutData $imutData, array $indicator): void
    {
        $currentYear = now()->year; // 2026

        // Generate 3 years worth of quarters (12 quarters total)
        for ($year = $currentYear; $year <= $currentYear + 2; $year++) {
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $quarterStart = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfDay();

                if ($quarter === 4) {
                    // Q4 ends on last day of March next year (to overlap with Q1)
                    $quarterEnd = Carbon::create($year + 1, 3, 31)->endOfDay();
                } else {
                    // Q1-Q3 end normally
                    $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay()->endOfDay();
                }

                $quarterName = "verion-{$year}-Q{$quarter}";

                $this->command->info("  🗓️ Quarter: {$quarterName} ({$quarterStart->format('Y-m-d')} to {$quarterEnd->format('Y-m-d')})");

                // Temporarily disable observers to avoid duplicate form templates
                \App\Models\ImutProfile::withoutEvents(function () use ($imutData, $quarterName, $quarterStart, $quarterEnd, $indicator, $quarter, $year, $currentYear) {
                    return ImutProfile::create([
                        'imut_data_id' => $imutData->id,
                        'version' => $quarterName,
                        'valid_from' => $quarterStart,
                        'valid_until' => $quarterEnd,
                        'start_period' => $quarterStart,
                        'end_period' => $quarterEnd,
                        'rationale' => 'Generated for testing purposes',
                        'quality_dimension' => 'Keselamatan',
                        'objective' => 'Monitor healthcare quality indicators',
                        'operational_definition' => 'Standard operational definition',
                        'indicator_type' => $indicator['type'],
                        'numerator_formula' => 'Numerator formula',
                        'denominator_formula' => 'Denominator formula',
                        'inclusion_criteria' => 'All applicable cases',
                        'exclusion_criteria' => 'None',
                        'data_source' => 'Hospital Information System',
                        'data_collection_frequency' => 'bulanan',
                        'analysis_plan' => 'Monthly analysis',
                        'target_operator' => '>=',
                        'target_value' => $this->generateTargetValue($indicator['type'], $quarter + ($year - $currentYear) * 4),
                        'analysis_period_type' => 'bulanan',
                        'analysis_period_value' => 3,
                        'data_collection_method' => 'Automatic',
                        'sampling_method' => 'All population',
                        'data_collection_tool' => 'Electronic system',
                        'responsible_person' => 'System Administrator'
                    ]);
                });
            }
        }
    }

    private function getUniqueIndicators(): array
    {
        return [
            ['title' => 'Angka Kematian Bayi', 'type' => 'outcome', 'unit' => 'per 1000 kelahiran hidup', 'polarity' => 'low'],
            ['title' => 'Angka Kematian Ibu', 'type' => 'outcome', 'unit' => 'per 100.000 kelahiran hidup', 'polarity' => 'low'],
            ['title' => 'Cakupan Imunisasi Dasar Lengkap', 'type' => 'output', 'unit' => '%', 'polarity' => 'high'],
            ['title' => 'Cakupan Pelayanan Kesehatan Ibu Hamil K4', 'type' => 'output', 'unit' => '%', 'polarity' => 'high'],
            ['title' => 'Prevalensi Stunting', 'type' => 'outcome', 'unit' => '%', 'polarity' => 'low'],
            ['title' => 'Cakupan Screening Kanker Serviks', 'type' => 'process', 'unit' => '%', 'polarity' => 'high'],
            ['title' => 'Angka Kesakitan DBD', 'type' => 'outcome', 'unit' => 'per 100.000 penduduk', 'polarity' => 'low'],
            ['title' => 'Cakupan Jamban Sehat', 'type' => 'output', 'unit' => '%', 'polarity' => 'high'],
            ['title' => 'Persentase Desa Siaga Aktif', 'type' => 'process', 'unit' => '%', 'polarity' => 'high'],
            ['title' => 'Cakupan Pelayanan Kesehatan Dasar', 'type' => 'output', 'unit' => '%', 'polarity' => 'high'],
            ['title' => 'Rasio Dokter per Penduduk', 'type' => 'input', 'unit' => 'per 1000 penduduk', 'polarity' => 'high'],
            ['title' => 'Cakupan Penanganan Pneumonia Balita', 'type' => 'process', 'unit' => '%', 'polarity' => 'high'],
            ['title' => 'Persentase Rumah Tangga Ber-PHBS', 'type' => 'outcome', 'unit' => '%', 'polarity' => 'high'],
            ['title' => 'Cakupan Kunjungan Neonatal Lengkap', 'type' => 'process', 'unit' => '%', 'polarity' => 'high'],
        ];
    }

    private function generateTargetValue(string $type, int $quarter): float
    {
        $baseValues = [
            'outcome' => 85.0,
            'output' => 90.0,
            'process' => 88.0,
            'input' => 75.0
        ];

        $baseValue = $baseValues[$type] ?? 80.0;
        $variation = ($quarter % 4 + 1) * 2.5; // Quarterly progression

        return round($baseValue + $variation, 1);
    }
}
