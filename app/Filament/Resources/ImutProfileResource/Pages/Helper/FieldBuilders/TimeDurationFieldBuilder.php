<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\DateTimePicker;


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
     * @param array $customLabels Custom labels for start_time and end_time fields
     * @return Grid
     */
    public static function create(
        string $fieldKey,
        bool $required = false,
        $visibleCondition = true,
        string $defaultThreshold = '00:20',
        string $thresholdType = 'less_than',
        array $customLabels = []
    ): Grid {
        return Grid::make(3)
            ->schema([
                Hidden::make($fieldKey . '_threshold_type')
                    ->default($thresholdType)
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, callable $set) use ($thresholdType, $fieldKey) {
                        $set($fieldKey . '_threshold_type', $thresholdType);
                    }),

                Hidden::make($fieldKey . '_valid_indicator')
                    ->default('0')
                    ->dehydrated(true),


                self::createValidationIndicator($fieldKey)
                    ->columnSpanFull(),

                Fieldset::make('Waktu Mulai dan Selesai')
                    ->columnSpanFull()
                    ->schema([
                        self::createStartDateTimePicker($fieldKey, $required, $customLabels),
                        self::createEndDateTimePicker($fieldKey, $required, $customLabels),
                    ]),

                self::createDurationDisplay($fieldKey)
                    ->columnSpanFull(),

                self::createThresholdPicker($fieldKey, $defaultThreshold)
                    ->columnSpanFull(),
            ])
            ->visible($visibleCondition)
            ->columnSpanFull();
    }

    /**
     * Create start time picker
     * 
     * @param string $fieldKey Base field key
     * @param bool $required Is required
     * @param array $customLabels Custom labels for start_time and end_time fields
     * @return TimePicker
     */
    public static function createStartTimePicker(string $fieldKey, bool $required, array $customLabels = []): TimePicker
    {
        $startTimeLabel = $customLabels['start_time'] ?? 'Waktu Mulai';

        return TimePicker::make($fieldKey . '_start_time')
            ->label($startTimeLabel)
            ->required($required)
            ->seconds(false)
            ->format('H:i')
            ->displayFormat('H:i')
            ->hoursStep(1)
            ->minutesStep(1)
            ->suffix('Jam:menit')
            ->debounce(1000)
            ->live()
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                // Combine with date if present, then validate on load
                $date = $get($fieldKey . '_start_date');
                if ($date) {
                    $combined = trim($date . ' ' . $state);
                    $set($fieldKey . '_start_time', $combined);
                } else {
                    $set($fieldKey . '_start_time', $state);
                }
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                $date = $get($fieldKey . '_start_date');
                if ($date) {
                    $set($fieldKey . '_start_time', trim($date . ' ' . $state));
                } else {
                    $set($fieldKey . '_start_time', $state);
                }
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
     * @param array $customLabels Custom labels for start_time and end_time fields
     * @return TimePicker
     */
    public static function createEndTimePicker(string $fieldKey, bool $required, array $customLabels = []): TimePicker
    {
        $endTimeLabel = $customLabels['end_time'] ?? 'Waktu Selesai';

        return TimePicker::make($fieldKey . '_end_time')
            ->label($endTimeLabel)
            ->required($required)
            ->seconds(false)
            ->format('H:i')
            ->displayFormat('H:i')
            ->hoursStep(1)
            ->minutesStep(1)
            ->suffix('Jam:menit')
            ->debounce(1000)
            ->live()
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                // Combine with date if present, then validate on load
                $date = $get($fieldKey . '_end_date');
                if ($date) {
                    $set($fieldKey . '_end_time', trim($date . ' ' . $state));
                } else {
                    $set($fieldKey . '_end_time', $state);
                }
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                $date = $get($fieldKey . '_end_date');
                if ($date) {
                    $set($fieldKey . '_end_time', trim($date . ' ' . $state));
                } else {
                    $set($fieldKey . '_end_time', $state);
                }
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);
            })
            ->reactive(); // Make it reactive to update validation indicator
    }

    /**
     * Create start date picker
     *
     * @param string $fieldKey
     * @return DateTimePicker
     */
    public static function createStartDateTimePicker(
        string $fieldKey,
        bool $required,
        array $customLabels = []
    ): DateTimePicker {
        $startTimeLabel = $customLabels['start_time'] ?? 'Waktu Mulai';

        return DateTimePicker::make($fieldKey . '_start_time')
            ->label($startTimeLabel)
            ->required($required)
            ->seconds(false)
            ->format('Y-m-d H:i')
            ->displayFormat('d M Y H:i')
            ->default(now())
            ->native(true)
            ->debounce(1000)
            ->live()
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                if (blank($state)) {
                    $set($fieldKey . '_start_time', now()->format('Y-m-d H:i'));
                }

                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';

                self::validateDurationAndSetIndicator(
                    $get,
                    $set,
                    $fieldKey,
                    $thresholdType
                );
            })
            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($fieldKey) {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';

                self::validateDurationAndSetIndicator(
                    $get,
                    $set,
                    $fieldKey,
                    $thresholdType
                );
            })
            ->reactive();
    }

    /**
     * Create end date picker
     *
     * @param string $fieldKey
     * @return DateTimePicker
     */
    public static function createEndDateTimePicker(
        string $fieldKey,
        bool $required,
        array $customLabels = []
    ): DateTimePicker {
        $endTimeLabel = $customLabels['end_time'] ?? 'Waktu Selesai';

        return DateTimePicker::make($fieldKey . '_end_time')
            ->label($endTimeLabel)
            ->required($required)
            ->seconds(false)
            ->format('Y-m-d H:i')
            ->displayFormat('d M Y H:i')
            ->default(now())
            ->native(true)
            ->debounce(1000)
            ->live()
            ->afterStateHydrated(function ($state, callable $set, callable $get) use ($fieldKey) {
                if (blank($state)) {
                    $set($fieldKey . '_end_time', now()->format('Y-m-d H:i'));
                }

                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';

                self::validateDurationAndSetIndicator(
                    $get,
                    $set,
                    $fieldKey,
                    $thresholdType
                );
            })
            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($fieldKey) {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';

                self::validateDurationAndSetIndicator(
                    $get,
                    $set,
                    $fieldKey,
                    $thresholdType
                );
            })
            ->reactive();
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
            ->label(false)
            ->columnSpanFull()
            ->content(function (callable $get) use ($fieldKey): HtmlString {
                $startTime = $get($fieldKey . '_start_time');
                $endTime = $get($fieldKey . '_end_time');

                if (!$startTime || !$endTime) {
                    return new HtmlString(<<<HTML
                    <div class="w-full rounded-xl border border-warning-200 bg-warning-50 p-4 shadow-sm dark:border-warning-500/30 dark:bg-warning-500/10">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-300">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-warning-900 dark:text-warning-100">
                                    Durasi belum bisa dihitung
                                </p>

                                <p class="mt-1 text-sm text-warning-800/80 dark:text-warning-100/80">
                                    Lengkapi waktu mulai dan waktu selesai terlebih dahulu.
                                </p>
                            </div>
                        </div>
                    </div>
                HTML);
                }

                $durationInMinutes = TimeUtility::calculateDurationInMinutes($startTime, $endTime);

                if ($durationInMinutes === null) {
                    return new HtmlString(<<<HTML
                    <div class="w-full rounded-xl border border-danger-200 bg-danger-50 p-4 shadow-sm dark:border-danger-500/30 dark:bg-danger-500/10">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-300">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-danger-900 dark:text-danger-100">
                                    Format waktu tidak valid
                                </p>

                                <p class="mt-1 text-sm text-danger-800/80 dark:text-danger-100/80">
                                    Periksa kembali format waktu mulai dan waktu selesai.
                                </p>
                            </div>
                        </div>
                    </div>
                HTML);
                }

                $hours = intdiv($durationInMinutes, 60);
                $minutes = $durationInMinutes % 60;

                $durationText = sprintf('%02d:%02d', $hours, $minutes);
                $durationDescription = $hours > 0
                    ? "{$hours} jam {$minutes} menit"
                    : "{$minutes} menit";

                return new HtmlString(<<<HTML
                <div class="w-full rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-950 dark:text-white">
                                    Durasi Terhitung
                                </p>

                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Selisih antara waktu mulai dan waktu selesai.
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                {$durationText}
                            </p>

                            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                {$durationDescription}
                            </p>
                        </div>
                    </div>
                </div>
            HTML);
            })
            ->reactive();
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
            ->visible(false)
            ->dehydratedWhenHidden(true)
            ->dehydrated(true);
    }

    /**
     * Create validation indicator
     * 
     * @param string $fieldKey Base field key
     * @return Placeholder
     */
    public static function createValidationIndicator(string $fieldKey): Placeholder
    {
        return Placeholder::make($fieldKey . '_valid_indicator_display')
            ->label(false)
            ->columnSpanFull()
            ->content(function (callable $get, callable $set) use ($fieldKey): HtmlString {
                $thresholdType = $get($fieldKey . '_threshold_type') ?? 'less_than';
                $thresholdTime = $get($fieldKey . '_valid_duration_setting') ?? '00:00';

                self::validateDurationAndSetIndicator($get, $set, $fieldKey, $thresholdType);

                $isValid = (string) ($get($fieldKey . '_valid_indicator') ?? '0') === '1';

                return self::renderValidationStatusCard(
                    isValid: $isValid,
                    ruleText: self::buildDurationRuleText($thresholdType, $thresholdTime),
                );
            })
            ->reactive();
    }

    private static function buildDurationRuleText(string $thresholdType, string $thresholdTime): string
    {
        $operator = $thresholdType === 'greater_than' ? '≥' : '≤';
        $operatorText = $thresholdType === 'greater_than'
            ? 'minimal'
            : 'maksimal';

        $durationText = self::formatDurationText($thresholdTime);

        return "Valid apabila durasi {$operatorText} {$durationText}.";
    }

    private static function formatDurationText(?string $time): string
    {
        if (!$time || !str_contains($time, ':')) {
            return '0 menit';
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time));

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        }

        if ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$minutes} menit";
    }

    private static function renderValidationStatusCard(bool $isValid, string $ruleText): HtmlString
    {
        $tone = $isValid ? 'success' : 'danger';

        $statusLabel = $isValid ? 'Valid' : 'Tidak Valid';

        $description = $isValid
            ? 'Durasi sudah memenuhi aturan validasi yang ditentukan.'
            : 'Durasi belum memenuhi aturan validasi yang ditentukan.';

        $iconPath = $isValid
            ? 'm4.5 12.75 6 6 9-13.5'
            : 'M6 18 18 6M6 6l12 12';

        return new HtmlString(<<<HTML
        <div class="w-full rounded-xl border border-{$tone}-200 bg-{$tone}-50 p-4 shadow-sm dark:border-{$tone}-500/30 dark:bg-{$tone}-500/10">
            <div class="flex flex-col gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-{$tone}-100 text-{$tone}-700 dark:bg-{$tone}-500/20 dark:text-{$tone}-300">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{$iconPath}" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-semibold text-{$tone}-900 dark:text-{$tone}-100">
                                Status Validasi
                            </p>

                            <span class="inline-flex items-center rounded-md bg-{$tone}-100 px-2 py-0.5 text-xs font-medium text-{$tone}-700 ring-1 ring-inset ring-{$tone}-600/20 dark:bg-{$tone}-500/20 dark:text-{$tone}-300 dark:ring-{$tone}-500/30">
                                {$statusLabel}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-{$tone}-800/80 dark:text-{$tone}-100/80">
                            {$description}
                        </p>
                    </div>
                </div>

                <div class="rounded-lg bg-white/70 px-3 py-2 text-xs font-medium text-{$tone}-800 ring-1 ring-{$tone}-600/20 dark:bg-white/5 dark:text-{$tone}-200 dark:ring-{$tone}-500/20">
                    {$ruleText}
                </div>
            </div>
        </div>
    HTML);
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
