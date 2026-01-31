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
     * @return Grid
     */
    public static function create(
        string $fieldKey,
        bool $required = false,
        $visibleCondition = true,
        string $defaultStartTime = '08:00',
        string $defaultEndTime = '17:00'
    ): Grid {
        return Grid::make(2)
            ->schema([
                Hidden::make($fieldKey . '_start_time')
                    ->default($defaultStartTime)
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, callable $set) use ($defaultStartTime, $fieldKey) {
                        $set($fieldKey . '_start_time', $defaultStartTime);
                    }),
                Hidden::make($fieldKey . '_end_time')
                    ->default($defaultEndTime)
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, callable $set) use ($defaultEndTime, $fieldKey) {
                        $set($fieldKey . '_end_time', $defaultEndTime);
                    }),
                self::createInputValuePicker($fieldKey, $required),
                self::createRangeDisplay($fieldKey),
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
     * @return Placeholder
     */
    public static function createRangeDisplay(string $fieldKey): Placeholder
    {
        return Placeholder::make($fieldKey . '_range_display')
            ->label('Rentang Waktu Valid')
            ->content(function (callable $get) use ($fieldKey) {
                $startTime = $get($fieldKey . '_start_time');
                $endTime = $get($fieldKey . '_end_time');

                if (!$startTime || !$endTime) {
                    return '⚠️ Rentang waktu belum diatur';
                }

                return "⏰ Rentang valid: {$startTime} - {$endTime}";
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
