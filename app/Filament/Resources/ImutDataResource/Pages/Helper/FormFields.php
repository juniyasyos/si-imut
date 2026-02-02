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
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\SelectFieldBuilder;
use Filament\Forms\Components\ToggleButtons;

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
                $historySuggestions = $field->history_suggestions ?? [];

                // Decode JSON if history_suggestions is stored as JSON string
                if (is_string($historySuggestions)) {
                    $historySuggestions = json_decode($historySuggestions, true) ?? [];
                }

                // Always use Select field for text inputs to enable history building
                $options = array_combine($historySuggestions, $historySuggestions); // value => label

                return SelectFieldBuilder::createSearchableSelect(
                    $field->field_key,
                    $baseConfig['label'],
                    $baseConfig['helperText'],
                    $options,
                    $field->validation_config['required'] ?? false,
                    $visibleCondition,
                    function ($newValue, $newLabel) use ($field) {
                        // Auto-add to history suggestions when user enters new value
                        $currentHistory = $field->history_suggestions ?? [];

                        // Decode if it's a JSON string
                        if (is_string($currentHistory)) {
                            $currentHistory = json_decode($currentHistory, true) ?? [];
                        }

                        // Add new value if not already exists
                        if (!in_array($newValue, $currentHistory)) {
                            $currentHistory[] = $newValue;

                            // Limit to 10 suggestions, keep most recent
                            $currentHistory = array_slice($currentHistory, -10);

                            // Update field in database
                            $field->update([
                                'history_suggestions' => $currentHistory
                            ]);
                        }
                    }
                );

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

                return SelectFieldBuilder::createSearchableSelect(
                    $field->field_key,
                    $baseConfig['label'],
                    $baseConfig['helperText'],
                    $options,
                    $field->validation_config['required'] ?? false,
                    $visibleCondition,
                    null, // default value
                    function ($newValue, $newLabel) {
                        // Callback untuk menambah opsi baru
                        // Dalam preview, kita tidak menyimpan ke database
                        // tapi opsi akan tersedia selama session
                    }
                );

            case 'multi_select':
                $options = [];
                foreach ($field->options as $option) {
                    $options[$option->option_value] = $option->option_text;
                }

                return SelectFieldBuilder::createMultiSelect(
                    $field->field_key,
                    $baseConfig['label'],
                    $baseConfig['helperText'],
                    $options,
                    $field->validation_config['required'] ?? false,
                    $visibleCondition
                );

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
}
