<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper;

use Carbon\Carbon;

/**
 * Utility class for time-related operations
 */
class TimeUtility
{
    /**
     * Convert time string to minutes
     * Accepts both H:i and H:i:s formats
     * 
     * @param string $time Time in H:i or H:i:s format
     * @return int Minutes (defaults to 480 = 8 hours on error)
     */
    public static function convertTimeToMinutes(string $time): int
    {
        try {
            // Try H:i:s format first, then H:i format
            $carbon = Carbon::createFromFormat('H:i:s', $time) ?: Carbon::createFromFormat('H:i', $time);
            return ($carbon->hour * 60) + $carbon->minute;
        } catch (\Exception $e) {
            return 480; // Default: 8 hours
        }
    }

    /**
     * Convert minutes to time string
     * 
     * @param int $minutes Minutes to convert
     * @return string Time in H:i:s format
     */
    public static function convertMinutesToTime(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d:%02d', $hours, $mins, 0);
    }

    /**
     * Calculate duration in minutes between two times
     * Accepts both H:i and H:i:s formats
     * 
     * @param string $startTime Start time (H:i or H:i:s format)
     * @param string $endTime End time (H:i or H:i:s format)
     * @return int|null Duration in minutes, or null if invalid
     */
    public static function calculateDurationInMinutes(string $startTime, string $endTime): ?int
    {
        try {
            // Try H:i:s format first, then H:i format for both start and end times
            try {
                $start = Carbon::createFromFormat('H:i:s', $startTime);
            } catch (\Exception $e) {
                $start = Carbon::createFromFormat('H:i', $startTime);
            }

            try {
                $end = Carbon::createFromFormat('H:i:s', $endTime);
            } catch (\Exception $e) {
                $end = Carbon::createFromFormat('H:i', $endTime);
            }

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            return $start->diffInMinutes($end);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check duration validity between start and end time
     * Accepts both H:i and H:i:s formats for all time parameters
     * 
     * @param string|null $startTime Start time (H:i or H:i:s format)
     * @param string|null $endTime End time (H:i or H:i:s format)
     * @param string $thresholdTime Threshold time (H:i or H:i:s format)
     * @return bool True if valid, false otherwise
     */
    public static function checkDurationValidity(?string $startTime, ?string $endTime, string $thresholdTime = '08:00:00', string $thresholdType = 'less_than'): bool
    {

        if (!$startTime || !$endTime) {
            return false;
        }

        try {
            $threshold = self::convertTimeToMinutes($thresholdTime);

            // Try H:i:s format first, then H:i format for both start and end times
            try {
                $start = Carbon::createFromFormat('H:i:s', $startTime);
            } catch (\Exception $e) {
                $start = Carbon::createFromFormat('H:i', $startTime);
            }

            try {
                $end = Carbon::createFromFormat('H:i:s', $endTime);
            } catch (\Exception $e) {
                $end = Carbon::createFromFormat('H:i', $endTime);
            }

            // Handle case where end time is next day
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $durationInMinutes = $start->diffInMinutes($end);

            dd($startTime, $endTime, $thresholdTime, $thresholdType, $threshold, $durationInMinutes);

            // Validate based on threshold type
            if ($thresholdType === 'greater_than') {
                return $durationInMinutes >= $threshold;
            } else {
                // Default to 'less_than'
                return $durationInMinutes <= $threshold;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Format time string to H:i:s if needed
     * 
     * @param string $time Time string
     * @return string Formatted time
     */
    public static function formatTime(string $time): string
    {
        try {
            $carbon = Carbon::parse($time);
            return $carbon->format('H:i:s');
        } catch (\Exception $e) {
            return $time;
        }
    }

    /**
     * Check if time string is valid
     * Accepts both H:i and H:i:s formats
     * 
     * @param string $time Time string
     * @return bool
     */
    public static function isValidTime(string $time): bool
    {
        try {
            // Try H:i:s format first, then H:i format
            Carbon::createFromFormat('H:i:s', $time) ?: Carbon::createFromFormat('H:i', $time);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
