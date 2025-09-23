<?php

namespace App\Services\Data;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class DateFormattingService
{
    /**
     * Generate X-axis labels from laporan collection
     */
    public function generateTimeLabels(Collection $laporans): array
    {
        return $laporans->map(function ($laporan) {
            return $this->formatPeriodLabel(
                $laporan->assessment_period_start,
                $laporan->assessment_period_end
            );
        })->toArray();
    }

    /**
     * Format period label for charts
     */
    public function formatPeriodLabel(?string $startDate, ?string $endDate): string
    {
        $start = $startDate ? Carbon::parse($startDate) : null;
        $end = $endDate ? Carbon::parse($endDate) : null;

        if (!$start || !$end) {
            return 'Tidak diketahui';
        }

        // Same month - show day range
        if ($start->month === $end->month && $start->year === $end->year) {
            return $start->day . ' - ' . $end->day . ' ' . $start->translatedFormat('F Y');
        }

        // Different months - show full range
        return $start->translatedFormat('j F') . ' - ' . $end->translatedFormat('j F Y');
    }

    /**
     * Format single date for display
     */
    public function formatDisplayDate(?string $date): string
    {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('j F Y');
        } catch (\Exception $e) {
            return 'Format tidak valid';
        }
    }

    /**
     * Format date range for display
     */
    public function formatDateRange(?string $startDate, ?string $endDate): string
    {
        if (!$startDate && !$endDate) {
            return 'Periode tidak ditentukan';
        }

        if (!$startDate) {
            return 'Sampai ' . $this->formatDisplayDate($endDate);
        }

        if (!$endDate) {
            return 'Dari ' . $this->formatDisplayDate($startDate);
        }

        return $this->formatPeriodLabel($startDate, $endDate);
    }

    /**
     * Generate month labels for reports
     */
    public function generateMonthLabels(int $year, int $startMonth = 1, int $endMonth = 12): array
    {
        $labels = [];

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $date = Carbon::createFromDate($year, $month, 1);
            $labels[] = $date->translatedFormat('F Y');
        }

        return $labels;
    }

    /**
     * Check if date is within period
     */
    public function isDateInPeriod(string $date, string $startPeriod, string $endPeriod): bool
    {
        try {
            $checkDate = Carbon::parse($date);
            $start = Carbon::parse($startPeriod);
            $end = Carbon::parse($endPeriod);

            return $checkDate->between($start, $end);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Calculate period duration in days
     */
    public function calculatePeriodDuration(?string $startDate, ?string $endDate): int
    {
        if (!$startDate || !$endDate) {
            return 0;
        }

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            return $start->diffInDays($end) + 1; // Include both start and end days
        } catch (\Exception $e) {
            return 0;
        }
    }
}
