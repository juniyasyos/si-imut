<?php

namespace App\Modules\FormEngine\Services;

use App\Services\Core\ImutCalculatorService;

/**
 * Service untuk menangani calculation logic di Filament Forms
 * Focus: Extract calculation dari Schema components
 */
class FormCalculationService
{
    public function __construct(
        private ImutCalculatorService $calculator
    ) {}

    /**
     * Update result calculation untuk form penilaian
     *
     * @param callable $set
     * @param callable $get
     * @return void
     */
    public function updatePenilaianResult(callable $set, callable $get): void
    {
        $numerator = (float) ($get('numerator_value') ?: 0);
        $denominator = (float) ($get('denominator_value') ?: 0);

        $result = $this->calculator->calculateImutResult($numerator, $denominator);

        $set('result_operation', $result['percentage']);
    }

    /**
     * Calculate end period berdasarkan analysis period
     *
     * @param callable $set
     * @param callable $get
     * @return void
     */
    public function calculateEndPeriod(callable $set, callable $get): void
    {
        $start = $get('start_period');
        $type = $get('analysis_period_type');
        $value = (int) $get('analysis_period_value');

        if (!$start || !$type || !$value) {
            return;
        }

        try {
            $startDate = \Illuminate\Support\Carbon::parse($start);
            $endDate = match ($type) {
                'mingguan' => $startDate->copy()->addWeeks($value),
                'bulanan' => $startDate->copy()->addMonths($value),
                default => $startDate,
            };

            $set('end_period', $endDate->format('Y-m-d'));
        } catch (\Exception $e) {
            logger()->error('Perhitungan end_period gagal:', [
                'start' => $start,
                'type' => $type,
                'value' => $value,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate numerator tidak lebih besar dari denominator untuk certain cases
     *
     * @param float $numerator
     * @param float $denominator
     * @return bool
     */
    public function isValidNumeratorDenominator(float $numerator, float $denominator): bool
    {
        if ($denominator <= 0) {
            return false;
        }

        // In some cases, numerator shouldn't exceed denominator
        // This can be configured based on business rules
        return true; // For now, allow any valid positive denominator
    }

    /**
     * Generate suggestion berdasarkan percentage result
     *
     * @param float $percentage
     * @param float $target
     * @param string $operator
     * @return string
     */
    public function generatePerformanceSuggestion(float $percentage, float $target, string $operator): string
    {
        $isAchieved = $this->calculator->isTargetAchieved($percentage, $operator, $target);

        if ($isAchieved) {
            return "Target tercapai! Nilai {$percentage}% sudah memenuhi standar {$target}%.";
        }

        $difference = abs($percentage - $target);

        return match ($operator) {
            '>=' => "Perlu peningkatan {$difference}% untuk mencapai target {$target}%.",
            '<=' => "Nilai melebihi batas maksimal {$difference}%. Target maksimal {$target}%.",
            '=' => "Selisih {$difference}% dari target {$target}%.",
            '>' => "Perlu peningkatan lebih dari {$difference}% untuk melampaui target {$target}%.",
            '<' => "Nilai terlalu tinggi {$difference}%. Harus di bawah {$target}%.",
            default => "Evaluasi manual diperlukan.",
        };
    }

    /**
     * Format display value untuk form fields
     *
     * @param float $value
     * @param string $type
     * @return string
     */
    public function formatDisplayValue(float $value, string $type = 'percentage'): string
    {
        return match ($type) {
            'percentage' => number_format($value, 2) . '%',
            'decimal' => number_format($value, 2),
            'integer' => number_format($value, 0),
            'currency' => 'Rp ' . number_format($value, 0, ',', '.'),
            default => (string) $value,
        };
    }
}
