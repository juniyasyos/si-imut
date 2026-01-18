<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class FormFields
{
    public static function createFormComponent($field)
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
                $visibleCondition = function ($get) use ($logic) {
                    $dependentValue = $get($logic['depends_on_field']);
                    return in_array($dependentValue, $logic['trigger_values']);
                };
            }
        }

        switch ($field->field_type) {
            case 'text':
                return TextInput::make($field->field_key)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->maxLength($field->validation_config['max_length'] ?? 255)
                    ->required($field->validation_config['required'] ?? false)
                    ->visible($visibleCondition);

            case 'number':
                return TextInput::make($field->field_key)
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

                return ToggleButtons::make($field->field_key)
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

                return CheckboxList::make($field->field_key)
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
                    return Radio::make($field->field_key)
                        ->label($baseConfig['label'])
                        ->helperText($baseConfig['helperText'])
                        ->options($options)
                        ->required($field->validation_config['required'] ?? false)
                        ->visible($visibleCondition)
                        ->live();
                } else {
                    return ToggleButtons::make($field->field_key)
                        ->label($baseConfig['label'])
                        ->helperText($baseConfig['helperText'])
                        ->required($field->validation_config['required'] ?? false)
                        ->visible($visibleCondition)
                        ->live();
                }

            case 'time_duration':
                return Section::make($baseConfig['label'])
                    ->description($baseConfig['helperText'])
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TimePicker::make($field->field_key . '_start_time')
                                    ->label('Waktu Mulai')
                                    ->required($field->validation_config['required'] ?? false)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) use ($field) {
                                        self::calculateDuration($get, $set, $field->field_key);
                                    }),

                                TimePicker::make($field->field_key . '_end_time')
                                    ->label('Waktu Selesai')
                                    ->required($field->validation_config['required'] ?? false)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) use ($field) {
                                        self::calculateDuration($get, $set, $field->field_key);
                                    }),

                                TextInput::make($field->field_key . '_duration')
                                    ->label('Durasi')
                                    ->readonly()
                                    ->placeholder('HH:MM')
                                    ->helperText('Dihitung otomatis dari selisih waktu mulai dan selesai'),
                            ]),
                    ])
                    ->visible($visibleCondition)
                    ->columnSpanFull();

            case 'time_range':
                return Section::make($baseConfig['label'])
                    ->description($baseConfig['helperText'])
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TimePicker::make($field->field_key . '_start_time')
                                    ->label('Waktu Mulai')
                                    ->required($field->validation_config['required'] ?? false),

                                TimePicker::make($field->field_key . '_end_time')
                                    ->label('Waktu Selesai')
                                    ->required($field->validation_config['required'] ?? false),
                            ]),
                    ])
                    ->visible($visibleCondition)
                    ->columnSpanFull();

            default:
                return TextInput::make($field->field_key)
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

    private static function calculateDuration(callable $get, callable $set, string $fieldKey): void
    {
        $startTime = $get($fieldKey . '_start_time');
        $endTime = $get($fieldKey . '_end_time');

        if ($startTime && $endTime) {
            try {
                $start = \Carbon\Carbon::createFromFormat('H:i', $startTime);
                $end = \Carbon\Carbon::createFromFormat('H:i', $endTime);

                // Handle case where end time is next day
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $duration = $start->diff($end);
                $hours = $duration->h;
                $minutes = $duration->i;

                $durationString = sprintf('%02d:%02d', $hours, $minutes);
                $set($fieldKey . '_duration', $durationString);
            } catch (\Exception $e) {
                $set($fieldKey . '_duration', '');
            }
        } else {
            $set($fieldKey . '_duration', '');
        }
    }
}
