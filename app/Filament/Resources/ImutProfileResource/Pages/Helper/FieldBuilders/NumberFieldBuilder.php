<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\TextInput;

/**
 * Builder for number input fields
 */
class NumberFieldBuilder
{
    /**
     * Create a number input field
     * 
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param int|null $min Minimum value
     * @param int|null $max Maximum value
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @return TextInput
     */
    public static function create(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        ?int $min = null,
        ?int $max = null,
        bool $required = false,
        $visibleCondition = true
    ): TextInput {
        return TextInput::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->numeric()
            ->minValue($min)
            ->maxValue($max)
            ->required($required)
            ->visible($visibleCondition);
    }
}
