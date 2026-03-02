<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\DatePicker;

/**
 * Builder for date picker fields
 */
class DateFieldBuilder
{
    /**
     * Create a date picker field
     *
     * @param string $fieldKey Field key
     * @param string $label Field label
     * @param string|null $helperText Helper text
     * @param bool $required Is required
     * @param mixed $visibleCondition Visibility condition
     * @param string|null $minDate Minimum selectable date (Y-m-d)
     * @param string|null $maxDate Maximum selectable date (Y-m-d)
     * @param string|null $defaultValue Default date value (Y-m-d)
     * @return DatePicker
     */
    public static function create(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        bool $required = false,
        $visibleCondition = true,
        ?string $minDate = null,
        ?string $maxDate = null,
        ?string $defaultValue = null
    ): DatePicker {
        $picker = DatePicker::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->required($required)
            ->visible($visibleCondition)
            ->displayFormat('d/m/Y')
            ->format('Y-m-d');

        if ($minDate !== null) {
            $picker->minDate($minDate);
        }

        if ($maxDate !== null) {
            $picker->maxDate($maxDate);
        }

        if ($defaultValue !== null) {
            $picker->default($defaultValue);
        }

        return $picker;
    }
}
