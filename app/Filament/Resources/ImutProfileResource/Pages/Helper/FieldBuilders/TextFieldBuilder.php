<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\TextInput;

/**
 * Builder for text input fields
 */
class TextFieldBuilder
{
    /**
     * Create a text input field
     * 
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param int $maxLength Maximum length
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @return TextInput
     */
    public static function create(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        int $maxLength = 255,
        bool $required = false,
        $visibleCondition = true
    ): TextInput {
        return TextInput::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->maxLength($maxLength)
            ->required($required)
            ->visible($visibleCondition);
    }
}
