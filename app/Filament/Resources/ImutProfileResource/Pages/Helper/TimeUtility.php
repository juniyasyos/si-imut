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
            $time = trim($time);
            if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $matches)) {
                $hours = (int)$matches[1];
                $minutes = (int)$matches[2];
                return ($hours * 60) + $minutes;
            }
            throw new \Exception('Invalid time format');
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
            // Detect whether input strings include a date part (YYYY-MM-DD or ISO T)
            $startHasDate = (bool) preg_match('/\d{4}-\d{2}-\d{2}/', $startTime) || strpos($startTime, 'T') !== false;
            $endHasDate = (bool) preg_match('/\d{4}-\d{2}-\d{2}/', $endTime) || strpos($endTime, 'T') !== false;

            if ($startHasDate || $endHasDate) {
                // Parse as full datetimes when at least one contains a date
                $start = Carbon::parse($startTime);
                $end = Carbon::parse($endTime);

                // If both values include explicit dates, respect them as-is.
                // If one of them lacks a date, allow crossing midnight by adding a day.
                if ((!$startHasDate || !$endHasDate) && $end->lessThan($start)) {
                    $end->addDay();
                }

                return $start->diffInMinutes($end);
            }

            // Fallback: time-only strings (H:i[:s]) — preserve previous behavior
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
    public static function checkDurationValidity(?string $startTime, ?string $endTime, string $thresholdTime, string $thresholdType = 'less_than'): bool
    {

        if (!$startTime || !$endTime) {
            return false;
        }

        try {
            $threshold = self::convertTimeToMinutes($thresholdTime);
            // Detect whether inputs include an explicit date part
            $startHasDate = (bool) preg_match('/\d{4}-\d{2}-\d{2}/', $startTime) || strpos($startTime, 'T') !== false;
            $endHasDate = (bool) preg_match('/\d{4}-\d{2}-\d{2}/', $endTime) || strpos($endTime, 'T') !== false;

            if ($startHasDate || $endHasDate) {
                $start = Carbon::parse($startTime);
                $end = Carbon::parse($endTime);

                // If one of the values lacks a date, allow crossing midnight by adding a day
                if ((!$startHasDate || !$endHasDate) && $end->lessThan($start)) {
                    $end->addDay();
                }

                $durationInMinutes = $start->diffInMinutes($end);
            } else {
                // Fallback to time-only parsing
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

                // Allow crossing midnight for time-only inputs
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $durationInMinutes = $start->diffInMinutes($end);
            }

            // Validate based on threshold type
            if ($thresholdType === 'greater_than') {
                $result = $durationInMinutes >= $threshold;
            } else {
                // Default to 'less_than'
                $result = $durationInMinutes <= $threshold;
            }

            return $result;
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
