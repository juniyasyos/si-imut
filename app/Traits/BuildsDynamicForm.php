<?php

namespace App\Traits;

use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\SelectFieldBuilder;
use App\Models\EnhancedFormField;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

trait BuildsDynamicForm
{
    /**
     * Generate form component from FormField model
     */
    protected function buildFormComponent(EnhancedFormField $field)
    {
        // Use FormFields helper for complex field types with 'responses.' prefix
        if (in_array($field->field_type, ['time_duration', 'time_range', 'single_select', 'multi_select', 'boolean'])) {
            return FormFields::createFormComponent($field, 'responses.');
        }

        return match ($field->field_type) {
            'text' => $this->buildTextFieldWithHistory($field),

            'textarea' => Textarea::make("responses.{$field->field_key}")
                ->label($field->field_label)
                ->required($field->is_critical_field)
                ->placeholder('Masukkan ' . strtolower($field->field_label))
                ->helperText($field->field_description)
                ->rows(4)
                ->maxLength(1000)
                ->columnSpanFull(),

            'number' => TextInput::make("responses.{$field->field_key}")
                ->label($field->field_label)
                ->numeric()
                ->required($field->is_critical_field)
                ->placeholder('0')
                ->helperText($field->field_description)
                ->minValue(0)
                ->step(1)
                ->prefix('📊'),

            'date' => DatePicker::make("responses.{$field->field_key}")
                ->label($field->field_label)
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required($field->is_critical_field)
                ->helperText($field->field_description)
                ->maxDate(now())
                ->prefix('📅'),

            'bool' => Checkbox::make("responses.{$field->field_key}")
                ->label($field->field_label)
                ->required($field->is_critical_field)
                ->helperText($field->field_description)
                ->inline(false)
                ->columnSpanFull(),

            'select' => Select::make("responses.{$field->field_key}")
                ->label($field->field_label)
                ->options($this->getFieldOptions($field))
                ->required($field->is_critical_field)
                ->helperText($field->field_description)
                ->searchable()
                ->placeholder('Pilih ' . strtolower($field->field_label)),

            'radio' => Radio::make("responses.{$field->field_key}")
                ->label($field->field_label)
                ->options($this->getFieldOptions($field))
                ->required($field->is_critical_field)
                ->helperText($field->field_description)
                ->inline()
                ->columnSpanFull(),

            'checkbox' => CheckboxList::make("responses.{$field->field_key}")
                ->label($field->field_label)
                ->options($this->getFieldOptions($field))
                ->required($field->is_critical_field)
                ->helperText($field->field_description)
                ->columns(2)
                ->columnSpanFull(),

            default => TextInput::make("responses.{$field->field_key}")
                ->label($field->field_label)
                ->required($field->is_critical_field)
                ->helperText($field->field_description)
                ->placeholder('Masukkan ' . strtolower($field->field_label)),
        };
    }

    /**
     * Get field options for select/radio/checkbox components
     */
    protected function getFieldOptions(EnhancedFormField $field): array
    {
        // Check if field has options relation
        if ($field->relationLoaded('options') && $field->options->isNotEmpty()) {
            return $field->options->pluck('option_label', 'option_value')->toArray();
        }

        // Fallback to validation_config if no options relation
        $validationConfig = $field->validation_config ?? [];
        $options = $validationConfig['options'] ?? [];

        if (is_array($options) && !empty($options)) {
            return array_combine($options, $options);
        }

        return [];
    }

    /**
     * Build array of form components from collection of FormFields
     */
    protected function buildFormFields($formFields): array
    {
        $components = [];

        foreach ($formFields as $field) {
            $components[] = $this->buildFormComponent($field);
        }

        return $components;
    }

    /**
     * Format field value for display
     */
    protected function formatFieldValue($value, string $type): string
    {
        if (is_null($value)) {
            return '-';
        }

        return match ($type) {
            'bool' => $value ? '✅ Ya' : '❌ Tidak',
            'date' => \Carbon\Carbon::parse($value)->format('d/m/Y'),
            'checkbox' => is_array($value) ? implode(', ', $value) : $value,
            default => $value,
        };
    }

    /**
     * Build text field with history suggestions capability
     */
    protected function buildTextFieldWithHistory(EnhancedFormField $field)
    {
        $historySuggestions = $field->history_suggestions ?? [];

        // Decode JSON if history_suggestions is stored as JSON string
        if (is_string($historySuggestions)) {
            $historySuggestions = json_decode($historySuggestions, true) ?? [];
        }

        // Always use Select field for text inputs to enable history building
        $options = array_combine($historySuggestions, $historySuggestions); // value => label

        return SelectFieldBuilder::createSearchableSelect(
            "responses.{$field->field_key}",
            $field->field_label,
            $field->field_description,
            $options,
            $field->is_critical_field,
            true, // visible condition
            null, // default value
            true, // allow custom input
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
    }
}
