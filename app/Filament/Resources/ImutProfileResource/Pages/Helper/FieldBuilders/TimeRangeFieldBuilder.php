<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Carbon\Carbon;

/**
 * Builder for time range fields with validation
 */
class TimeRangeFieldBuilder
{
    /**
     * Create a time range field with validation
     *
     * @param string $fieldKey Base field key
     * @param bool $required Whether the field is required
     * @param mixed $visibleCondition Visibility condition
     * @param string $defaultStartTime Default start time
     * @param string $defaultEndTime Default end time
     * @param array $customLabels Custom labels for start_time and end_time fields
     * @return Grid
     */
    public static function create(
        string $fieldKey,
        bool $required = false,
        $visibleCondition = true,
        string $defaultStartTime = '08:00',
        string $defaultEndTime = '17:00',
        array $customLabels = []
    ): Grid {
        return Grid::make(2)
            ->schema([
                Hidden::make($fieldKey . '_start_time')
                    ->default($defaultStartTime)
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, callable $set) use ($defaultStartTime, $fieldKey) {
                        $value = blank($state) ? $defaultStartTime : $state;
                        $set($fieldKey . '_start_time', self::normalizeTime($value));
                    }),
                Hidden::make($fieldKey . '_end_time')
                    ->default($defaultEndTime)
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, callable $set) use ($defaultEndTime, $fieldKey) {
                        $value = blank($state) ? $defaultEndTime : $state;
                        $set($fieldKey . '_end_time', self::normalizeTime($value));
                    }),
                self::createInputValuePicker($fieldKey, $required),
                self::createRangeDisplay($fieldKey, $customLabels),
                self::createValidationIndicator($fieldKey),
            ])
            ->visible($visibleCondition)
            ->columnSpanFull();
    }

    /**
     * Create input value time picker
     *
     * @param string $fieldKey Base field key
     * @param bool $required Is required
     * @return TimePicker
     */
    public static function createInputValuePicker(string $fieldKey, bool $required): TimePicker
    {
        return TimePicker::make($fieldKey . '_input_value')
            ->label('Nilai Waktu Input')
            ->required($required)
            ->seconds(false)
            ->format('H:i')
            ->displayFormat('H:i')
            ->debounce(1000)
            ->live()
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                self::validateInputValue($get, $set, $fieldKey);
            })
            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                self::validateInputValue($get, $set, $fieldKey);
            })
            ->reactive();
    }

    /**
     * Create range display
     *
     * @param string $fieldKey Base field key
     * @param array $customLabels Custom labels for start_time and end_time fields
     * @return Placeholder
     */
    public static function createRangeDisplay(string $fieldKey, array $customLabels = []): Placeholder
    {
        $startTimeLabel = $customLabels['start_time'] ?? 'Waktu Mulai';
        $endTimeLabel = $customLabels['end_time'] ?? 'Waktu Selesai';

        return Placeholder::make($fieldKey . '_range_display')
            ->label('Rentang Waktu Valid')
            ->content(function (callable $get) use ($fieldKey, $startTimeLabel, $endTimeLabel) {
                $startTime = $get($fieldKey . '_start_time');
                $endTime = $get($fieldKey . '_end_time');

                if (!$startTime || !$endTime) {
                    return '⚠️ Rentang waktu belum diatur';
                }

                $startTimeFormatted = self::normalizeTime($startTime);
                $endTimeFormatted = self::normalizeTime($endTime);

                return "⏰ Rentang valid: {$startTimeLabel} ({$startTimeFormatted}) - {$endTimeLabel} ({$endTimeFormatted})";
            })
            ->reactive()
            ->columnSpan(1);
    }

    /**
     * Create validation indicator
     *
     * @param string $fieldKey Base field key
     * @return ToggleButtons
     */
    public static function createValidationIndicator(string $fieldKey): ToggleButtons
    {
        return ToggleButtons::make($fieldKey . '_valid_indicator')
            ->label('Status Validasi')
            ->options([
                '1' => '✅ Valid',
                '0' => '❌ Tidak Valid',
            ])
            ->inline()
            ->disabled()
            ->dehydrated(false)
            ->default('0')
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                self::validateInputValue($get, $set, $fieldKey);
            })
            ->reactive()
            ->columnSpan(1);
    }

    /**
     * Normalize a time string to H:i format
     *
     * @param string $time
     * @return string
     */
    private static function normalizeTime(string $time): string
    {
        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Exception $e) {
            return $time;
        }
    }

    /**
     * Validate input value against range
     *
     * @param callable $get Getter function
     * @param callable $set Setter function
     * @param string $fieldKey Base field key
     * @return void
     */
    public static function validateInputValue(callable $get, callable $set, string $fieldKey): void
    {
        $inputValue = $get($fieldKey . '_input_value');
        $startTime = $get($fieldKey . '_start_time');
        $endTime = $get($fieldKey . '_end_time');

        $isValid = self::isInputValueValid($inputValue, $startTime, $endTime);
        $set($fieldKey . '_valid_indicator', $isValid ? '1' : '0');
    }

    /**
     * Check if input value is within the range
     *
     * @param string|null $inputValue
     * @param string|null $startTime
     * @param string|null $endTime
     * @return bool
     */
    public static function isInputValueValid(?string $inputValue, ?string $startTime, ?string $endTime): bool
    {
        if (!$inputValue || !$startTime || !$endTime) {
            return false;
        }

        try {
            $today = Carbon::today();
            $input = $today->copy()->setTimeFromTimeString($inputValue);
            $start = $today->copy()->setTimeFromTimeString($startTime);
            $end = $today->copy()->setTimeFromTimeString($endTime);

            return $input->between($start, $end, true); // inclusive
        } catch (\Exception $e) {
            return false;
        }
    }
}
