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
     * @param string|null $defaultValue Default value
     * @param array $historySuggestions History suggestions for datalist
     * @param callable|null $onBlurCallback Callback dipanggil setelah user selesai mengetik (onBlur).
     *                                      Signature: function(string $value): void
     * @return TextInput
     */
    public static function create(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        int $maxLength = 255,
        bool $required = false,
        $visibleCondition = true,
        ?string $defaultValue = null,
        array $historySuggestions = [],
        ?callable $onBlurCallback = null
    ): TextInput {
        $input = TextInput::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->maxLength($maxLength)
            ->required($required)
            ->visible($visibleCondition);

        if ($defaultValue) {
            $input->default($defaultValue);
        }

        if (!empty($historySuggestions)) {
            $input->datalist($historySuggestions);
        }

        if ($onBlurCallback !== null) {
            $input->live(onBlur: true)
                ->afterStateUpdated(function (?string $state) use ($onBlurCallback) {
                    if (filled($state)) {
                        $onBlurCallback($state);
                    }
                });
        }

        return $input;
    }
}
