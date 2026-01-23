<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;

/**
 * Builder for select fields (single and multi)
 */
class SelectFieldBuilder
{
    /**
     * Create a single select field
     * 
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param array $options Options array
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @return ToggleButtons
     */
    public static function createSingleSelect(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        array $options = [],
        bool $required = false,
        $visibleCondition = true
    ): ToggleButtons {
        return ToggleButtons::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->options($options)
            ->inline()
            ->required($required)
            ->visible($visibleCondition)
            ->live();
    }

    /**
     * Create a multi select field
     * 
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param array $options Options array
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @return CheckboxList
     */
    public static function createMultiSelect(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        array $options = [],
        bool $required = false,
        $visibleCondition = true
    ): CheckboxList {
        return CheckboxList::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->options($options)
            ->required($required)
            ->bulkToggleable()
            ->visible($visibleCondition)
            ->live()
            ->columns(1);
    }

    /**
     * Extract options from field options collection
     * 
     * @param mixed $fieldOptions Field options
     * @return array
     */
    public static function extractOptions($fieldOptions): array
    {
        $options = [];
        foreach ($fieldOptions as $option) {
            $options[$option->option_value] = $option->option_text;
        }
        return $options;
    }
}
