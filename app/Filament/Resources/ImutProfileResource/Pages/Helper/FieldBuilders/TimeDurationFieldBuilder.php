<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;

/**
 * Builder for time duration fields with validation
 */
class TimeDurationFieldBuilder
{
    /**
     * Create a time duration field with validation
     * 
     * @param string $fieldKey Base field key
     * @param bool $required Whether the field is required
     * @param mixed $visibleCondition Visibility condition
     * @param string $defaultThreshold Default threshold time (HH:MM:SS)
     * @param string $thresholdType Type of threshold validation ('less_than' or 'greater_than')
     * @return Grid
     */
    public static function create(
        string $fieldKey,
        bool $required = false,
        $visibleCondition = true,
        string $defaultThreshold = '00:20',
        string $thresholdType = 'less_than'
    ): Grid {
        return Grid::make(3)
            ->schema([
                Hidden::make($fieldKey . '_threshold_type')
                    ->default($thresholdType)
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, callable $set) use ($thresholdType, $fieldKey) {
                        // Force set the threshold type value
                        $set($fieldKey . '_threshold_type', $thresholdType);
                    }),
                self::createStartTimePicker($fieldKey, $required),
                self::createEndTimePicker($fieldKey, $required),
                self::createDurationDisplay($fieldKey),
                self::createThresholdPicker($fieldKey, $defaultThreshold)
                    ->columnSpan(2),
                self::createValidationIndicator($fieldKey)
                    ->columnSpan(1),
            ])
            ->visible($visibleCondition)
            ->columnSpanFull();
    }

    /**
     * Create start time picker
     * 
     * @param string $fieldKey Base field key
     * @param bool $required Is required
     * @return TimePicker
     */
    public static function createStartTimePicker(string $fieldKey, bool $required): TimePicker
    {
        return TimePicker::make($fieldKey . '_start_time')
            ->label('Waktu Mulai')
            ->required($required)
            ->seconds(false)
            ->debounce(1000)
            ->live()
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                // Validate on load
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->reactive(); // Make it reactive to update validation indicator
    }

    /**
     * Create end time picker
     * 
     * @param string $fieldKey Base field key
     * @param bool $required Is required
     * @return TimePicker
     */
    public static function createEndTimePicker(string $fieldKey, bool $required): TimePicker
    {
        return TimePicker::make($fieldKey . '_end_time')
            ->label('Waktu Selesai')
            ->required($required)
            ->seconds(false)
            ->debounce(1000)
            ->live()
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                // Validate on load
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->reactive(); // Make it reactive to update validation indicator
    }

    /**
     * Create duration display field
     * 
     * @param string $fieldKey Base field key
     * @return Placeholder
     */
    public static function createDurationDisplay(string $fieldKey): Placeholder
    {
        return Placeholder::make($fieldKey . '_duration_display')
            ->label('Durasi Terhitung')
            ->content(function (callable $get) use ($fieldKey) {
                $startTime = $get($fieldKey . '_start_time');
                $endTime = $get($fieldKey . '_end_time');

                if (!$startTime || !$endTime) {
                    return '⚠️ Lengkapi waktu mulai dan selesai';
                }

                $durationInMinutes = TimeUtility::calculateDurationInMinutes($startTime, $endTime);

                if ($durationInMinutes === null) {
                    return '❌ Format waktu tidak valid';
                }

                $hours = intdiv($durationInMinutes, 60);
                $minutes = $durationInMinutes % 60;

                $durationText = sprintf('%02d:%02d', $hours, $minutes);

                if ($hours > 0) {
                    $durationText .= " ({$hours} jam {$minutes} menit)";
                } else {
                    $durationText .= " ({$minutes} menit)";
                }

                return "⏱️ {$durationText}";
            })
            ->reactive() // Make it reactive to update when start/end times change
            ->columnSpan(1);
    }

    /**
     * Create threshold picker
     *
     * @param string $fieldKey Base field key
     * @param string $defaultValue Default threshold value
     * @return TimePicker
     */
    public static function createThresholdPicker(string $fieldKey, string $defaultValue): TimePicker
    {
        return TimePicker::make($fieldKey . '_valid_duration_setting')
            ->label(function (callable $get) use ($fieldKey) {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                $typeLabel = $thresholdType === 'greater_than' ? 'minimal' : 'maksimal';
                return "Threshold Durasi Valid ({$typeLabel} jam:menit)";
            })
            ->helperText(function (callable $get) use ($fieldKey) {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                if ($thresholdType === 'greater_than') {
                    return 'Durasi minimal yang dianggap valid. Durasi harus lebih dari atau sama dengan threshold ini.';
                } else {
                    return 'Durasi maksimal yang dianggap valid. Durasi harus kurang dari atau sama dengan threshold ini.';
                }
            })
            ->seconds(false)
            ->default($defaultValue)
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey, $defaultValue) {
                // Set default value if field is empty
                if (blank($state)) {
                    $set($fieldKey . '_valid_duration_setting', $defaultValue);
                }
                // Validate on load
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->live()
            ->reactive(); // Make it reactive to update label when threshold type changes
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
            ->label(function (callable $get) use ($fieldKey) {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                $thresholdTime = $get($fieldKey . '_valid_duration_setting');
                $typeText = $thresholdType === 'greater_than' ? '≥' : '≤';

                return "Status Validasi (durasi {$typeText} {$thresholdTime})";
            })
            ->options([
                '1' => '✅ Valid',
                '0' => '❌ Tidak Valid',
            ])
            ->inline()
            ->disabled()
            ->dehydrated(false)
            ->default('0')
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                // Validate on load
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->reactive();
    }

    /**
     * Validate duration and update indicator
     *
     * @param callable $get Getter function
     * @param callable $set Setter function
     * @param string $fieldKey Base field key
     * @param string $thresholdType Type of threshold validation ('less_than' or 'greater_than')
     * @return void
     */
    public static function validateDurationAndSetIndicator(callable $get, callable $set, string $fieldKey, string $thresholdType = 'less_than'): void
    {
        $isValid = self::isDurationValid($get, $fieldKey, $thresholdType);
        $set($fieldKey . '_valid_indicator', $isValid ? '1' : '0');
    }

    /**
     * Check if duration is valid using getter
     * 
     * @param callable $get Getter function
     * @param string $fieldKey Base field key
     * @param string $thresholdType Type of threshold validation ('less_than' or 'greater_than')
     * @return bool
     */
    public static function isDurationValid(callable $get, string $fieldKey, string $thresholdType = 'less_than'): bool
    {
        $startTime = $get($fieldKey . '_start_time');
        $endTime = $get($fieldKey . '_end_time');
        $thresholdTime = $get($fieldKey . '_valid_duration_setting') ?? '00:15:00';

        return TimeUtility::checkDurationValidity($startTime, $endTime, $thresholdTime, $thresholdType);
    }
}
