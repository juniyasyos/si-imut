<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\ToggleButtons;

/**
 * Builder for boolean fields
 */
class BooleanFieldBuilder
{
    /**
     * Create a boolean field
     * 
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param array $options Options array
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @return Radio|ToggleButtons
     */
    public static function create(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        array $options = [],
        bool $required = false,
        $visibleCondition = true
    ) {
        if (count($options) > 0) {
            return Radio::make($fieldKey)
                ->label($label)
                ->helperText($helperText)
                ->options($options)
                ->required($required)
                ->visible($visibleCondition)
                ->live();
        }

        return ToggleButtons::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->required($required)
            ->visible($visibleCondition)
            ->live();
    }
}
