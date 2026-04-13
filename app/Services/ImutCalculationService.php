<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service untuk kalkulasi IMUT yang digunakan berulang kali
 */
class ImutCalculationService
{
    /**
     * Generate SQL expression untuk perhitungan persentase
     *
     * @param string $numeratorColumn Nama kolom numerator
     * @param string $denominatorColumn Nama kolom denominator
     * @param string $alias Alias untuk hasil (default: 'percentage')
     * @return string SQL expression
     */
    public static function percentageExpression(
        string $numeratorColumn,
        string $denominatorColumn,
        string $alias = 'percentage'
    ): string {
        return "CEIL(
            CASE
                WHEN {$denominatorColumn} > 0 THEN
                    ({$numeratorColumn} * 100.0 / NULLIF({$denominatorColumn}, 0)) * 100
                ELSE 0
            END
        ) / 100 as {$alias}";
    }

    /**
     * Generate SQL expression untuk menghitung jumlah yang sudah diisi
     *
     * @param string $numeratorColumn Nama kolom numerator
     * @param string $denominatorColumn Nama kolom denominator
     * @param string $alias Alias untuk hasil (default: 'filled_count')
     * @return string SQL expression
     */
    public static function filledCountExpression(
        string $numeratorColumn = 'numerator_value',
        string $denominatorColumn = 'denominator_value',
        string $alias = 'filled_count'
    ): string {
        return "SUM(
            CASE
                WHEN {$numeratorColumn} IS NOT NULL
                AND {$denominatorColumn} IS NOT NULL
                AND {$denominatorColumn} != 0
                THEN 1 ELSE 0
            END
        ) as {$alias}";
    }

    /**
     * Generate SQL expression untuk completion percentage
     *
     * @param string $filledCountExpression Expression untuk filled count (tanpa alias)
     * @param string $totalCountExpression Expression untuk total count
     * @param string $alias Alias untuk hasil (default: 'percentage')
     * @return string SQL expression
     */
    public static function completionPercentageExpression(
        string $filledCountExpression,
        string $totalCountExpression = 'COUNT(imut_penilaians.id)',
        string $alias = 'percentage'
    ): string {
        return "ROUND(
            CASE
                WHEN {$totalCountExpression} > 0 THEN
                    {$filledCountExpression} * 100.0 / {$totalCountExpression}
                ELSE 0
            END, 2
        ) as {$alias}";
    }

    /**
     * Hitung persentase di PHP (untuk non-query calculations)
     *
     * @param float|null $numerator
     * @param float|null $denominator
     * @param int $precision
     * @return float
     */
    public static function calculatePercentage(
        ?float $numerator,
        ?float $denominator,
        int $precision = 2
    ): float {
        if ($denominator === null || $denominator == 0 || $numerator === null) {
            return 0.0;
        }

        $percentage = ($numerator / $denominator) * 100;
        return ceil($percentage * 100) / 100;
    }

    /**
     * Check apakah nilai memenuhi standar berdasarkan operator
     *
     * @param float $value Nilai yang dicek
     * @param float $standard Nilai standar
     * @param string $operator Operator perbandingan (=, >=, <=, >, <)
     * @return bool
     */
    public static function meetsStandard(
        float $value,
        float $standard,
        string $operator
    ): bool {
        return match ($operator) {
            '=' => abs($value - $standard) < 0.01,
            '>=' => $value >= $standard,
            '<=' => $value <= $standard,
            '>' => $value > $standard,
            '<' => $value < $standard,
            default => false,
        };
    }

    /**
     * Generate SQL expression untuk mengecek apakah nilai memenuhi standar
     *
     * @param string $valueExpression Expression untuk nilai yang dicek (tanpa 'as alias')
     * @param string $standardColumn Kolom yang berisi nilai standar
     * @param string $operatorColumn Kolom yang berisi operator
     * @return string SQL expression (boolean)
     */
    public static function meetsStandardExpression(
        string $valueExpression,
        string $standardColumn,
        string $operatorColumn
    ): string {
        // Hilangkan 'as alias' dari value expression jika ada
        $valueExpression = preg_replace('/\s+as\s+\w+$/i', '', trim($valueExpression));

        return "(
            CASE {$operatorColumn}
                WHEN '=' THEN ABS(({$valueExpression}) - {$standardColumn}) < 0.01
                WHEN '>=' THEN ({$valueExpression}) >= {$standardColumn}
                WHEN '<=' THEN ({$valueExpression}) <= {$standardColumn}
                WHEN '>' THEN ({$valueExpression}) > {$standardColumn}
                WHEN '<' THEN ({$valueExpression}) < {$standardColumn}
                ELSE 0
            END
        )";
    }

    /**
     * Generate SQL expression untuk sum dengan default 0
     *
     * @param string $column
     * @param string $alias
     * @return string
     */
    public static function sumExpression(string $column, string $alias): string
    {
        return "COALESCE(SUM({$column}), 0) as {$alias}";
    }
}
