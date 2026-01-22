<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;

class FormFields
{
    public static function createFormComponent($field, $prefix = '')
    {
        $baseConfig = [
            'label' => $field->field_label,
            'helperText' => $field->field_description,
        ];

        // Add conditional logic: set visible condition
        $visibleCondition = true;
        if ($field->conditional_logic) {
            $logic = $field->conditional_logic;
            if ($logic['condition_type'] === 'show_when') {
                $visibleCondition = function ($get) use ($logic, $prefix) {
                    $dependentValue = $get($prefix . $logic['depends_on_field']);
                    return in_array($dependentValue, $logic['trigger_values']);
                };
            }
        }

        // Build field key with prefix
        $fieldKey = $prefix . $field->field_key;

        switch ($field->field_type) {
            case 'text':
                return TextInput::make($fieldKey)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->maxLength($field->validation_config['max_length'] ?? 255)
                    ->required($field->validation_config['required'] ?? false)
                    ->visible($visibleCondition);

            case 'number':
                return TextInput::make($fieldKey)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->numeric()
                    ->minValue($field->validation_config['min'] ?? null)
                    ->maxValue($field->validation_config['max'] ?? null)
                    ->required($field->validation_config['required'] ?? false)
                    ->visible($visibleCondition);

            case 'single_select':
                $options = [];
                foreach ($field->options as $option) {
                    $options[$option->option_value] = $option->option_text;
                }

                return ToggleButtons::make($fieldKey)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->options($options)
                    ->inline()
                    ->required($field->validation_config['required'] ?? false)
                    ->visible($visibleCondition)
                    ->live();

            case 'multi_select':
                $options = [];
                foreach ($field->options as $option) {
                    $options[$option->option_value] = $option->option_text;
                }

                return CheckboxList::make($fieldKey)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->options($options)
                    ->required($field->validation_config['required'] ?? false)
                    ->bulkToggleable()
                    ->visible($visibleCondition)
                    ->live()
                    ->columns(1);

            case 'boolean':
                $options = [];
                foreach ($field->options as $option) {
                    $options[$option->option_value] = $option->option_text;
                }

                if (count($options) > 0) {
                    return Radio::make($fieldKey)
                        ->label($baseConfig['label'])
                        ->helperText($baseConfig['helperText'])
                        ->options($options)
                        ->required($field->validation_config['required'] ?? false)
                        ->visible($visibleCondition)
                        ->live();
                } else {
                    return ToggleButtons::make($fieldKey)
                        ->label($baseConfig['label'])
                        ->helperText($baseConfig['helperText'])
                        ->required($field->validation_config['required'] ?? false)
                        ->visible($visibleCondition)
                        ->live();
                }

            case 'time_duration':
                return Grid::make(2)
                    ->schema([
                        TimePicker::make($fieldKey . '_start_time')
                            ->label('Waktu Mulai')
                            ->required($field->validation_config['required'] ?? false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                                self::validateDurationAndSetIndicator($get, $set, $fieldKey);
                            }),

                        TimePicker::make($fieldKey . '_end_time')
                            ->label('Waktu Selesai')
                            ->required($field->validation_config['required'] ?? false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                                self::validateDurationAndSetIndicator($get, $set, $fieldKey);
                            }),

                        TimePicker::make($fieldKey . '_valid_duration_setting')
                            ->label('Threshold Durasi Valid (jam:menit)')
                            ->helperText('Durasi maksimal yang dianggap valid dalam format jam:menit')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) use ($fieldKey) {
                                self::validateDurationAndSetIndicator($get, $set, $fieldKey);
                            }),

                        ToggleButtons::make($fieldKey . '_valid_indicator')
                            ->label('Status Validasi')
                            ->options([
                                '1' => '✅ Valid',
                                '0' => '❌ Tidak Valid',
                            ])
                            ->inline()
                            ->disabled()
                            ->extraAttributes(['readonly' => true])
                            ->default('0'),
                    ])
                    ->visible($visibleCondition)
                    ->columnSpanFull();

            case 'time_range':
                return Grid::make(2)
                    ->schema([
                        TimePicker::make($field->field_key . '_start_time')
                            ->label('Waktu Mulai')
                            ->required($field->validation_config['required'] ?? false),

                        TimePicker::make($field->field_key . '_end_time')
                            ->label('Waktu Selesai')
                            ->required($field->validation_config['required'] ?? false),
                    ])
                    ->visible($visibleCondition)
                    ->columnSpanFull();

            default:
                return TextInput::make($fieldKey)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->required($field->validation_config['required'] ?? false)
                    ->visible($visibleCondition);
        }
    }

    public static function isFieldVisible($field, $data): bool
    {
        if (!$field->conditional_logic) {
            return true;
        }

        $logic = $field->conditional_logic;
        $dependentValue = $data[$logic['depends_on_field']] ?? null;

        if ($logic['condition_type'] === 'show_when') {
            return in_array($dependentValue, $logic['trigger_values']);
        }

        return true;
    }

    private static function validateDurationAndSetIndicator(callable $get, callable $set, string $fieldKey): void
    {
        $startTime = $get($fieldKey . '_start_time');
        $endTime = $get($fieldKey . '_end_time');
        $thresholdTime = $get($fieldKey . '_valid_duration_setting') ?? '08:00:00';
        $threshold = self::convertTimeToMinutes($thresholdTime);

        if ($startTime && $endTime) {
            try {
                $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
                $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);

                // Handle case where end time is next day
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $durationInMinutes = $start->diffInMinutes($end);

                // Validasi: durasi >= 0 dan <= threshold
                $isValid = $durationInMinutes >= 0 && $durationInMinutes <= $threshold;

                // Force update indicator by setting boolean value
                $set($fieldKey . '_valid_indicator', $isValid ? '1' : '0');
            } catch (\Exception $e) {
                $set($fieldKey . '_valid_indicator', '0');
            }
        } else {
            $set($fieldKey . '_valid_indicator', '0');
        }
    }

    private static function isDurationValid(callable $get, string $fieldKey): bool
    {
        $startTime = $get($fieldKey . '_start_time');
        $endTime = $get($fieldKey . '_end_time');
        $thresholdTime = $get($fieldKey . '_valid_duration_setting') ?? '08:00:00';
        $threshold = self::convertTimeToMinutes($thresholdTime);

        if (!$startTime || !$endTime) {
            return false;
        }

        try {
            $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
            $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);

            // Handle case where end time is next day
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $durationInMinutes = $start->diffInMinutes($end);

            return $durationInMinutes >= 0 && $durationInMinutes <= $threshold;
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function convertTimeToMinutes(string $time): int
    {
        try {
            $carbon = \Carbon\Carbon::createFromFormat('H:i:s', $time);
            return ($carbon->hour * 60) + $carbon->minute;
        } catch (\Exception $e) {
            return 480; 
        }
    }

    public static function convertMinutesToTime(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d:%02d', $hours, $mins, 0);
    }
}
