<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;

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
                Hidden::make($fieldKey . '_valid_indicator')
                    ->default('0')
                    ->dehydrated(true),
                self::createRangeDisplay($fieldKey, $customLabels),
                Fieldset::make($fieldKey . '_input_fieldset')
                    ->label('Input Nilai Waktu')
                    ->columnSpanFull()
                    ->schema([
                        self::createValidationIndicator($fieldKey),
                        self::createInputValuePicker($fieldKey, $required),
                    ])
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
            ->columnSpanFull()
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
            ->label(false)
            ->content(function (callable $get) use ($fieldKey, $startTimeLabel, $endTimeLabel): HtmlString {
                $startTime = $get($fieldKey . '_start_time');
                $endTime = $get($fieldKey . '_end_time');

                if (!$startTime || !$endTime) {
                    return new HtmlString(<<<HTML
                    <div class="w-full rounded-xl border border-warning-200 bg-warning-50 p-4 shadow-sm dark:border-warning-500/30 dark:bg-warning-500/10">
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-300">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-warning-900 dark:text-warning-100">
                                    Rentang waktu belum diatur
                                </p>

                                <p class="mt-1 text-sm text-warning-800/80 dark:text-warning-100/80">
                                    Lengkapi {$startTimeLabel} dan {$endTimeLabel} terlebih dahulu agar validasi waktu dapat dihitung.
                                </p>
                            </div>
                        </div>
                    </div>
                HTML);
                }

                $startTimeFormatted = self::normalizeTime($startTime);
                $endTimeFormatted = self::normalizeTime($endTime);

                return new HtmlString(<<<HTML
                <div class="w-full rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-slate-900">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                                        Rentang Waktu Valid
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Periode waktu yang digunakan sebagai batas valid pengisian indikator.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {$startTimeLabel}
                                    </span>

                                    <span class="rounded-md bg-white px-2 py-0.5 text-xs font-medium text-gray-500 ring-1 ring-gray-200 dark:bg-slate-900 dark:text-gray-400 dark:ring-white/10">
                                        Mulai
                                    </span>
                                </div>

                                <p class="mt-2 text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                    {$startTimeFormatted}
                                </p>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {$endTimeLabel}
                                    </span>

                                    <span class="rounded-md bg-white px-2 py-0.5 text-xs font-medium text-gray-500 ring-1 ring-gray-200 dark:bg-slate-900 dark:text-gray-400 dark:ring-white/10">
                                        Selesai
                                    </span>
                                </div>

                                <p class="mt-2 text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                    {$endTimeFormatted}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            HTML);
            })
            ->reactive()
            ->columnSpanFull();
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
            ->content(function (callable $get, callable $set) use ($fieldKey): HtmlString {
                self::validateInputValue($get, $set, $fieldKey);

                $isValid = (string) ($get($fieldKey . '_valid_indicator') ?? '0') === '1';

                if ($isValid) {
                    return new HtmlString(<<<HTML
                    <div class="w-full rounded-xl border border-success-200 bg-success-50 p-4 shadow-sm dark:border-success-500/30 dark:bg-success-500/10">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-300">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold text-success-900 dark:text-success-100">
                                        Status Validasi
                                    </p>

                                    <span class="inline-flex items-center rounded-md bg-success-100 px-2 py-0.5 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-500/20 dark:text-success-300 dark:ring-success-500/30">
                                        Valid
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-success-800/80 dark:text-success-100/80">
                                    Input sudah memenuhi aturan validasi waktu yang ditentukan.
                                </p>
                            </div>
                        </div>
                    </div>
                HTML);
                }

                return new HtmlString(<<<HTML
                <div class="w-full rounded-xl border border-danger-200 bg-danger-50 p-4 shadow-sm dark:border-danger-500/30 dark:bg-danger-500/10">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-300">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-danger-900 dark:text-danger-100">
                                    Status Validasi
                                </p>

                                <span class="inline-flex items-center rounded-md bg-danger-100 px-2 py-0.5 text-xs font-medium text-danger-700 ring-1 ring-inset ring-danger-600/20 dark:bg-danger-500/20 dark:text-danger-300 dark:ring-danger-500/30">
                                    Tidak Valid
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-danger-800/80 dark:text-danger-100/80">
                                Input belum memenuhi aturan validasi waktu. Periksa kembali nilai, waktu mulai, dan waktu selesai.
                            </p>
                        </div>
                    </div>
                </div>
            HTML);
            })
            ->reactive()
            ->columnSpanFull();
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
