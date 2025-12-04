<?php

namespace App\Traits;

use App\Models\FormField;
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
    protected function buildFormComponent(FormField $field)
    {
        return match ($field->type) {
            'text' => TextInput::make("responses.{$field->key}")
                ->label($field->label)
                ->required($field->is_required)
                ->placeholder('Masukkan ' . strtolower($field->label))
                ->helperText($field->description)
                ->maxLength(255),

            'textarea' => Textarea::make("responses.{$field->key}")
                ->label($field->label)
                ->required($field->is_required)
                ->placeholder('Masukkan ' . strtolower($field->label))
                ->helperText($field->description)
                ->rows(4)
                ->maxLength(1000)
                ->columnSpanFull(),

            'number' => TextInput::make("responses.{$field->key}")
                ->label($field->label)
                ->numeric()
                ->required($field->is_required)
                ->placeholder('0')
                ->helperText($field->description)
                ->minValue(0)
                ->step(1)
                ->prefix('📊'),

            'date' => DatePicker::make("responses.{$field->key}")
                ->label($field->label)
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required($field->is_required)
                ->helperText($field->description)
                ->maxDate(now())
                ->prefix('📅'),

            'bool' => Checkbox::make("responses.{$field->key}")
                ->label($field->label)
                ->required($field->is_required)
                ->helperText($field->description)
                ->inline(false)
                ->columnSpanFull(),

            'select' => Select::make("responses.{$field->key}")
                ->label($field->label)
                ->options(is_array($field->options) ? array_combine($field->options, $field->options) : [])
                ->required($field->is_required)
                ->helperText($field->description)
                ->searchable()
                ->placeholder('Pilih ' . strtolower($field->label)),

            'radio' => Radio::make("responses.{$field->key}")
                ->label($field->label)
                ->options(is_array($field->options) ? array_combine($field->options, $field->options) : [])
                ->required($field->is_required)
                ->helperText($field->description)
                ->inline()
                ->columnSpanFull(),

            'checkbox' => CheckboxList::make("responses.{$field->key}")
                ->label($field->label)
                ->options(is_array($field->options) ? array_combine($field->options, $field->options) : [])
                ->required($field->is_required)
                ->helperText($field->description)
                ->columns(2)
                ->columnSpanFull(),

            default => TextInput::make("responses.{$field->key}")
                ->label($field->label)
                ->required($field->is_required)
                ->helperText($field->description)
                ->placeholder('Masukkan ' . strtolower($field->label)),
        };
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
}
