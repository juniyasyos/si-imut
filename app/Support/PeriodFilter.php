<?php

namespace App\Support;

use Carbon\Carbon;

class PeriodFilter
{
    /**
     * Get date range based on period type and value
     *
     * @param  string  $type  Type: yearly, quarterly, semester, custom
     * @param  string|int  $value  Year for yearly, "2026-Q1" for quarterly, "2026-S1" for semester, "2026-01,2026-03" for custom
     * @return array  ['start' => Carbon, 'end' => Carbon]
     */
    public static function getDateRange(string $type, string|int $value): array
    {
        $value = (string) $value;

        return match ($type) {
            'yearly' => self::getYearlyRange($value),
            'quarterly' => self::getQuarterlyRange($value),
            'semester' => self::getSemesterRange($value),
            'custom' => self::getCustomRange($value),
            default => self::getYearlyRange($value),
        };
    }

    /**
     * Get yearly date range
     */
    private static function getYearlyRange(string $year): array
    {
        $start = Carbon::createFromDate($year, 1, 1);
        $end = Carbon::createFromDate($year, 12, 31);

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get quarterly date range
     * Format: "2026-Q1" atau "2026-Q2" dll
     */
    private static function getQuarterlyRange(string $value): array
    {
        [$year, $quarter] = explode('-Q', $value);
        $quarter = (int) $quarter;

        if ($quarter < 1 || $quarter > 4) {
            throw new \InvalidArgumentException("Quarter must be between 1 and 4");
        }

        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        $start = Carbon::createFromDate($year, $startMonth, 1);
        $end = Carbon::createFromDate($year, $endMonth, 1)->endOfMonth();

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get semester date range
     * Format: "2026-S1" atau "2026-S2"
     */
    private static function getSemesterRange(string $value): array
    {
        [$year, $semester] = explode('-S', $value);
        $semester = (int) $semester;

        if ($semester < 1 || $semester > 2) {
            throw new \InvalidArgumentException("Semester must be 1 or 2");
        }

        $startMonth = ($semester - 1) * 6 + 1;
        $endMonth = $startMonth + 5;

        $start = Carbon::createFromDate($year, $startMonth, 1);
        $end = Carbon::createFromDate($year, $endMonth, 1)->endOfMonth();

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get custom month range
     * Format: "2026-01,2026-03" (from January 2026 to March 2026)
     */
    private static function getCustomRange(string $value): array
    {
        [$startMonth, $endMonth] = explode(',', $value);

        $start = Carbon::createFromFormat('Y-m', $startMonth);
        $end = Carbon::createFromFormat('Y-m', $endMonth)->endOfMonth();

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get all months within date range
     */
    public static function getMonthsInRange(Carbon $start, Carbon $end): array
    {
        $months = [];
        $current = $start->copy()->startOfMonth();

        while ($current->lte($end)) {
            $months[] = [
                'date' => $current->copy(),
                'label' => $current->translatedFormat('M Y'),
                'value' => $current->format('Y-m'),
            ];
            $current->addMonth();
        }

        return $months;
    }

    /**
     * Get quarter label
     */
    public static function getQuarterLabel(int $month): string
    {
        $quarter = (int) ceil($month / 3);
        return "Triwulan $quarter";
    }

    /**
     * Get semester label
     */
    public static function getSemesterLabel(int $month): string
    {
        $semester = $month <= 6 ? 1 : 2;
        return "Semester $semester";
    }

    /**
     * Format period label
     */
    public static function formatPeriodLabel(string $type, string $value): string
    {
        $range = self::getDateRange($type, $value);

        return match ($type) {
            'yearly' => "Tahun {$value}",
            'quarterly' => str_replace('-Q', ' Triwulan ', $value),
            'semester' => str_replace('-S', ' Semester ', $value),
            'custom' => $range['start']->translatedFormat('F Y') . ' - ' . $range['end']->translatedFormat('F Y'),
            default => $value,
        };
    }
}
