<?php

namespace App\Filament\Resources\ImutDataResource\Pages\Helper;

use App\Filament\Resources\ImutDataResource;
use App\Models\ImutData;
use App\Models\FormTemplate;
use App\Services\FormBuilder\FormDataService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class FormFields
{
    public static function createFormComponent($field)
    {
        $baseConfig = [
            'label' => $field->field_label,
            'helperText' => $field->field_description,
        ];

        // Add conditional logic: set disabled if not visible
        $disabledCondition = false;
        if ($field->conditional_logic) {
            $logic = $field->conditional_logic;
            if ($logic['condition_type'] === 'show_when') {
                $disabledCondition = function ($get) use ($logic) {
                    $dependentValue = $get($logic['depends_on_field']);
                    return !in_array($dependentValue, $logic['trigger_values']);
                };
            }
        }

        switch ($field->field_type) {
            case 'text':
                return TextInput::make($field->field_key)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->maxLength($field->validation_config['max_length'] ?? 255)
                    ->required($disabledCondition)
                    ->disabled($disabledCondition);

            case 'number':
                return TextInput::make($field->field_key)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->numeric()
                    ->minValue($field->validation_config['min'] ?? null)
                    ->maxValue($field->validation_config['max'] ?? null)
                    ->required($disabledCondition)
                    ->disabled($disabledCondition);

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
                    ->required($disabledCondition)
                    ->disabled($disabledCondition)
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
                    ->required($disabledCondition)
                    ->bulkToggleable()
                    ->disabled($disabledCondition)
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
                        ->required($disabledCondition)
                        ->disabled($disabledCondition)
                        ->live();
                } else {
                    return ToggleButtons::make($field->field_key)
                        ->label($baseConfig['label'])
                        ->helperText($baseConfig['helperText'])
                        ->required($disabledCondition)
                        ->disabled($disabledCondition)
                        ->live();
                }

            default:
                return TextInput::make($field->field_key)
                    ->label($baseConfig['label'])
                    ->helperText($baseConfig['helperText'])
                    ->required($disabledCondition)
                    ->disabled($disabledCondition);
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
}
