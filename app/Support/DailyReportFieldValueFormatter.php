<?php

namespace App\Support;

use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeRangeFieldBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;
use App\Modules\FormEngine\Models\EnhancedFormField;
use Carbon\Carbon;

/**
 * Normalizes and formats stored FieldResponse values for display and Excel export,
 * supporting all legacy and current field_value shapes without mutating database records.
 */
class DailyReportFieldValueFormatter
{
    private const BOOLEAN_TRUTHY = ['1', 'true', 'ya', 'yes'];

    private const BOOLEAN_FALSY = ['0', 'false', 'tidak', 'no'];

    /**
     * Format a field response value for Excel export.
     * Always returns a scalar (string|int|float), never an array.
     */
    public static function formatForExport(EnhancedFormField $field, mixed $fieldValue): string|int|float
    {
        if ($fieldValue === null || $fieldValue === '' || $fieldValue === []) {
            return '';
        }

        return match ($field->field_type) {
            'boolean', 'bool' => self::formatBoolean($field, $fieldValue),
            'single_select', 'select', 'radio' => self::formatSingleSelect($field, $fieldValue),
            'multi_select', 'checkbox', 'conditional_trigger', 'compliance_checker' => self::formatMultiSelect($field, $fieldValue),
            'time_duration' => self::formatTimeDuration($fieldValue),
            'time_range' => self::formatTimeRange($field, $fieldValue),
            'number', 'rating_scale' => self::formatNumber($fieldValue),
            'date' => self::formatDate($fieldValue, 'd/m/Y'),
            'datetime' => self::formatDate($fieldValue, 'd/m/Y H:i'),
            default => self::formatGeneric($fieldValue),
        };
    }

    /**
     * Unwrap single-element or sequential arrays stored by legacy/edit save paths
     * into a plain scalar (or comma-separated string if multiple scalars exist).
     */
    public static function unwrapScalar(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if ($value === []) {
            return null;
        }

        $items = self::flattenNonEmptyScalars($value);

        if ($items === []) {
            return null;
        }

        if (count($items) === 1) {
            return $items[0];
        }

        return implode(', ', array_map('strval', $items));
    }

    /**
     * Format a time_duration field into separate Excel columns:
     * start, end, duration difference, and compliance status.
     *
     * @return array{start: string, end: string, duration: string, status: string}
     */
    public static function formatTimeDurationColumns(mixed $fieldValue): array
    {
        if (! is_array($fieldValue)) {
            $scalar = trim((string) ($fieldValue ?? ''));

            return [
                'start' => '',
                'end' => '',
                'duration' => $scalar,
                'status' => '',
            ];
        }

        $startTime = $fieldValue['start_time'] ?? null;
        $endTime = $fieldValue['end_time'] ?? null;
        $duration = $fieldValue['duration'] ?? null;
        $validIndicator = $fieldValue['valid_indicator'] ?? null;

        $hasStart = filled($startTime);
        $hasEnd = filled($endTime);
        $hasDuration = filled($duration);

        if (! $hasStart && ! $hasEnd && ! $hasDuration) {
            return [
                'start' => '',
                'end' => '',
                'duration' => '',
                'status' => '',
            ];
        }

        $durationText = '';
        if ($hasStart && $hasEnd) {
            $minutes = TimeUtility::calculateDurationInMinutes((string) $startTime, (string) $endTime);
            if ($minutes !== null) {
                $durationText = self::formatDurationMinutes($minutes);
            }
        } elseif ($hasDuration) {
            $durationText = "{$duration} menit";
        }

        $statusText = self::resolveValidityText($validIndicator) ?? '';

        return [
            'start' => $hasStart ? (string) $startTime : '',
            'end' => $hasEnd ? (string) $endTime : '',
            'duration' => $durationText,
            'status' => $statusText,
        ];
    }

    /**
     * Format a time_range field into separate Excel columns:
     * input time value and compliance status against the configured range.
     *
     * @return array{input_value: string, status: string}
     */
    public static function formatTimeRangeColumns(EnhancedFormField $field, mixed $fieldValue): array
    {
        if (! is_array($fieldValue)) {
            $scalar = trim((string) ($fieldValue ?? ''));

            return [
                'input_value' => $scalar,
                'status' => '',
            ];
        }

        $inputValue = $fieldValue['input_value'] ?? null;

        if (! filled($inputValue)) {
            return [
                'input_value' => '',
                'status' => '',
            ];
        }

        $storedStart = $fieldValue['start_time'] ?? null;
        $storedEnd = $fieldValue['end_time'] ?? null;
        $config = is_array($field->validation_config) ? $field->validation_config : [];
        $startTime = filled($storedStart) ? $storedStart : ($config['default_start_time'] ?? null);
        $endTime = filled($storedEnd) ? $storedEnd : ($config['default_end_time'] ?? null);

        $validIndicator = $fieldValue['valid_indicator'] ?? null;
        $isValid = null;

        if (filled($storedStart) && filled($storedEnd) && $validIndicator !== null && $validIndicator !== '') {
            $isValid = ((string) $validIndicator === '1' || $validIndicator === true);
        } elseif (filled($startTime) && filled($endTime)) {
            $isValid = TimeRangeFieldBuilder::isInputValueValid(
                (string) $inputValue,
                (string) $startTime,
                (string) $endTime
            );
        }

        return [
            'input_value' => (string) $inputValue,
            'status' => $isValid === null ? '' : ($isValid ? 'Sesuai' : 'Tidak Sesuai'),
        ];
    }

    /**
     * Resolve the boundary range label (e.g. "08:00 - 17:00") for a time_range field.
     */
    public static function resolveTimeRangeBoundaryLabel(EnhancedFormField $field, iterable $responses = []): ?string
    {
        $config = is_array($field->validation_config) ? $field->validation_config : [];
        $configStart = $config['default_start_time'] ?? null;
        $configEnd = $config['default_end_time'] ?? null;

        if (filled($configStart) && filled($configEnd)) {
            return "{$configStart} - {$configEnd}";
        }

        foreach ($responses as $response) {
            $fieldResponse = $response->fieldResponses->firstWhere('form_field_id', $field->id);
            $val = $fieldResponse?->field_value;

            if (is_array($val) && filled($val['start_time'] ?? null) && filled($val['end_time'] ?? null)) {
                return "{$val['start_time']} - {$val['end_time']}";
            }
        }

        return null;
    }

    public static function formatValidationStatus(?string $status): string
    {
        return match ($status) {
            'valid' => 'Valid',
            'invalid' => 'Tidak Valid',
            default => 'Belum Divalidasi',
        };
    }

    public static function sanitizeSheetTitle(?string $title): string
    {
        $sanitized = str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', (string) $title);
        $sanitized = trim((string) preg_replace('/\s+/', ' ', $sanitized));
        $truncated = mb_substr($sanitized, 0, 31);

        return $truncated !== '' ? $truncated : 'Monitoring';
    }

    private static function formatBoolean(EnhancedFormField $field, mixed $fieldValue): string
    {
        $scalar = self::unwrapScalar($fieldValue);

        if ($scalar === null || $scalar === '') {
            return '';
        }

        if ($field->options->isNotEmpty()) {
            $matched = self::findOptionByValue($field, $scalar);
            if ($matched !== null) {
                return (string) $matched->option_text;
            }
        }

        if (is_bool($scalar)) {
            return $scalar ? 'Ya' : 'Tidak';
        }

        if (is_int($scalar) || is_float($scalar)) {
            return ((int) $scalar) === 1 ? 'Ya' : 'Tidak';
        }

        $normalized = strtolower(trim((string) $scalar));

        if (in_array($normalized, self::BOOLEAN_TRUTHY, true)) {
            return 'Ya';
        }

        if (in_array($normalized, self::BOOLEAN_FALSY, true)) {
            return 'Tidak';
        }

        return (string) $scalar;
    }

    private static function formatSingleSelect(EnhancedFormField $field, mixed $fieldValue): string
    {
        $scalar = self::unwrapScalar($fieldValue);

        if ($scalar === null || $scalar === '') {
            return '';
        }

        $option = self::findOptionByValue($field, $scalar);

        return $option !== null ? (string) $option->option_text : (string) $scalar;
    }

    private static function formatMultiSelect(EnhancedFormField $field, mixed $fieldValue): string
    {
        $values = is_array($fieldValue) ? $fieldValue : [$fieldValue];
        $labels = [];

        foreach ($values as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            if (is_array($item)) {
                $flattened = self::formatGeneric($item);
                if ($flattened !== '') {
                    $labels[] = $flattened;
                }

                continue;
            }

            $option = self::findOptionByValue($field, $item);
            $labels[] = $option !== null ? (string) $option->option_text : (string) $item;
        }

        return implode(', ', $labels);
    }

    private static function formatTimeDuration(mixed $fieldValue): string
    {
        if (! is_array($fieldValue)) {
            return (string) $fieldValue;
        }

        $startTime = $fieldValue['start_time'] ?? null;
        $endTime = $fieldValue['end_time'] ?? null;
        $duration = $fieldValue['duration'] ?? null;
        $validIndicator = $fieldValue['valid_indicator'] ?? null;

        $hasStart = filled($startTime);
        $hasEnd = filled($endTime);

        if (! $hasStart && ! $hasEnd) {
            if (filled($duration)) {
                return self::appendValidityLabel("{$duration} menit", $validIndicator);
            }

            return '';
        }

        $rangeText = match (true) {
            $hasStart && $hasEnd => "{$startTime} - {$endTime}",
            $hasStart => "{$startTime} -",
            default => "- {$endTime}",
        };

        $durationText = null;
        if ($hasStart && $hasEnd) {
            $minutes = TimeUtility::calculateDurationInMinutes((string) $startTime, (string) $endTime);
            if ($minutes !== null) {
                $durationText = self::formatDurationMinutes($minutes);
            }
        } elseif (filled($duration)) {
            $durationText = "{$duration} menit";
        }

        $metaParts = [];
        if ($durationText !== null) {
            $metaParts[] = $durationText;
        }

        $validityText = self::resolveValidityText($validIndicator);
        if ($validityText !== null) {
            $metaParts[] = $validityText;
        }

        if ($metaParts === []) {
            return $rangeText;
        }

        return $rangeText . ' (' . implode(', ', $metaParts) . ')';
    }

    private static function formatTimeRange(EnhancedFormField $field, mixed $fieldValue): string
    {
        if (! is_array($fieldValue)) {
            return (string) $fieldValue;
        }

        $inputValue = $fieldValue['input_value'] ?? null;
        $storedStart = $fieldValue['start_time'] ?? null;
        $storedEnd = $fieldValue['end_time'] ?? null;

        $config = is_array($field->validation_config) ? $field->validation_config : [];
        $startTime = filled($storedStart) ? $storedStart : ($config['default_start_time'] ?? null);
        $endTime = filled($storedEnd) ? $storedEnd : ($config['default_end_time'] ?? null);

        if (! filled($inputValue)) {
            if (! array_key_exists('input_value', $fieldValue) && filled($storedStart) && filled($storedEnd)) {
                return "{$storedStart} - {$storedEnd}";
            }

            return '';
        }

        $validIndicator = $fieldValue['valid_indicator'] ?? null;
        $isValid = null;

        if (filled($storedStart) && filled($storedEnd) && $validIndicator !== null && $validIndicator !== '') {
            $isValid = ((string) $validIndicator === '1' || $validIndicator === true);
        } elseif (filled($startTime) && filled($endTime)) {
            $isValid = TimeRangeFieldBuilder::isInputValueValid(
                (string) $inputValue,
                (string) $startTime,
                (string) $endTime
            );
        }

        if ($isValid !== null && filled($startTime) && filled($endTime)) {
            $statusText = $isValid ? 'Sesuai' : 'Tidak Sesuai';

            return "{$inputValue} ({$statusText}, rentang {$startTime} - {$endTime})";
        }

        if ($isValid !== null) {
            return self::appendValidityLabel((string) $inputValue, $isValid ? '1' : '0');
        }

        return (string) $inputValue;
    }

    private static function formatNumber(mixed $fieldValue): string|int|float
    {
        $scalar = self::unwrapScalar($fieldValue);

        if ($scalar === null || $scalar === '') {
            return '';
        }

        if (is_numeric($scalar)) {
            $str = (string) $scalar;

            return str_contains($str, '.') ? (float) $scalar : (int) $scalar;
        }

        return (string) $scalar;
    }

    private static function formatDate(mixed $fieldValue, string $format): string
    {
        $scalar = self::unwrapScalar($fieldValue);

        if ($scalar === null || $scalar === '') {
            return '';
        }

        try {
            return Carbon::parse((string) $scalar)->format($format);
        } catch (\Throwable) {
            return (string) $scalar;
        }
    }

    private static function formatGeneric(mixed $fieldValue): string
    {
        if (! is_array($fieldValue)) {
            return (string) $fieldValue;
        }

        if ($fieldValue === []) {
            return '';
        }

        if (array_is_list($fieldValue)) {
            $items = self::flattenNonEmptyScalars($fieldValue);

            return implode(', ', array_map('strval', $items));
        }

        // Associative array fallback: render key-value pairs cleanly without JSON brackets
        $parts = [];
        foreach ($fieldValue as $key => $value) {
            if ($key === 'type' || $value === null || $value === '' || $value === []) {
                continue;
            }

            $rendered = is_array($value)
                ? self::formatGeneric($value)
                : (is_bool($value) ? ($value ? 'Ya' : 'Tidak') : (string) $value);

            if ($rendered !== '') {
                $parts[] = "{$key}: {$rendered}";
            }
        }

        return implode(', ', $parts);
    }

    private static function findOptionByValue(EnhancedFormField $field, mixed $value): ?object
    {
        $target = (string) $value;

        return $field->options->first(
            fn ($option) => (string) $option->option_value === $target
        );
    }

    /**
     * @return array<int, string|int|float|bool>
     */
    private static function flattenNonEmptyScalars(array $values): array
    {
        $result = [];

        foreach ($values as $item) {
            if (is_array($item)) {
                foreach (self::flattenNonEmptyScalars($item) as $nested) {
                    $result[] = $nested;
                }

                continue;
            }

            if ($item !== null && $item !== '') {
                $result[] = $item;
            }
        }

        return $result;
    }

    private static function formatDurationMinutes(int $totalMinutes): string
    {
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        }

        if ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$minutes} menit";
    }

    private static function resolveValidityText(mixed $validIndicator): ?string
    {
        if ($validIndicator === null || $validIndicator === '') {
            return null;
        }

        return ((string) $validIndicator === '1' || $validIndicator === true)
            ? 'Sesuai'
            : 'Tidak Sesuai';
    }

    private static function appendValidityLabel(string $text, mixed $validIndicator): string
    {
        $validityText = self::resolveValidityText($validIndicator);

        return $validityText !== null ? "{$text} ({$validityText})" : $text;
    }
}
