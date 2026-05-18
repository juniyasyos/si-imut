<?php

namespace App\Services\Core;

/**
 * Service untuk menangani semua perhitungan terkait IMUT
 * Focus: Pure calculation logic, no database dependencies
 */
class ImutCalculatorService
{
    /**
     * Menghitung persentase dari numerator dan denominator
     *
     * @param float $numerator
     * @param float $denominator
     * @param int $precision
     * @return float
     */
    public function calculatePercentage(float $numerator, float $denominator, int $precision = 2): float
    {
        if ($denominator == 0) {
            return 0.0;
        }

        $percentage = ($numerator / $denominator) * 100;

        return ceil($percentage * 100) / 100;
    }

    /**
     * Mengevaluasi apakah nilai mencapai target berdasarkan operator
     *
     * @param float $value
     * @param string $operator
     * @param float $target
     * @return bool
     */
    public function isTargetAchieved(float $value, string $operator, float $target): bool
    {
        return match ($operator) {
            '=' => $value == $target,
            '>=' => $value >= $target,
            '<=' => $value <= $target,
            '>' => $value > $target,
            '<' => $value < $target,
            default => false,
        };
    }

    /**
     * Menghitung hasil operasi IMUT dari numerator dan denominator
     *
     * @param float $numerator
     * @param float $denominator
     * @return array{percentage: float, is_valid: bool}
     */
    public function calculateImutResult(float $numerator, float $denominator): array
    {
        $isValid = $denominator > 0;
        $percentage = $this->calculatePercentage($numerator, $denominator);

        return [
            'percentage' => $percentage,
            'is_valid' => $isValid
        ];
    }

    /**
     * Evaluasi komprehensif penilaian IMUT
     *
     * @param float $numerator
     * @param float $denominator
     * @param float $target
     * @param string $operator
     * @return array{percentage: float, is_achieved: bool, is_valid: bool}
     */
    public function evaluatePenilaian(float $numerator, float $denominator, float $target, string $operator): array
    {
        $result = $this->calculateImutResult($numerator, $denominator);

        $isAchieved = $result['is_valid']
            ? $this->isTargetAchieved($result['percentage'], $operator, $target)
            : false;

        return [
            'percentage' => $result['percentage'],
            'is_achieved' => $isAchieved,
            'is_valid' => $result['is_valid']
        ];
    }

    /**
     * Batch evaluation untuk multiple penilaian
     *
     * @param array $penilaians Array of ['numerator' => float, 'denominator' => float, 'target' => float, 'operator' => string]
     * @return array
     */
    public function batchEvaluatePenilaian(array $penilaians): array
    {
        $results = [];

        foreach ($penilaians as $index => $penilaian) {
            $results[$index] = $this->evaluatePenilaian(
                $penilaian['numerator'] ?? 0,
                $penilaian['denominator'] ?? 0,
                $penilaian['target'] ?? 0,
                $penilaian['operator'] ?? '>='
            );
        }

        return $results;
    }

    /**
     * Menghitung statistik dari batch evaluations
     *
     * @param array $evaluationResults hasil dari batchEvaluatePenilaian
     * @return array{total: int, achieved: int, percentage_achieved: float}
     */
    public function calculateAchievementStats(array $evaluationResults): array
    {
        $total = count($evaluationResults);
        $achieved = array_sum(array_column($evaluationResults, 'is_achieved'));

        $percentageAchieved = $total > 0
            ? round(($achieved / $total) * 100, 2)
            : 0.0;

        return [
            'total' => $total,
            'achieved' => $achieved,
            'percentage_achieved' => $percentageAchieved
        ];
    }
}
