<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper;

/**
 * Handle conditional logic for form fields
 */
class ConditionalLogicHandler
{
    /**
     * Get visibility condition for a field
     * 
     * @param object|null $conditionalLogic Conditional logic configuration
     * @param string $prefix Field prefix
     * @return bool|callable Visibility condition
     */
    public static function getVisibilityCondition($conditionalLogic, string $prefix = '')
    {
        if (!$conditionalLogic) {
            return true;
        }

        $logic = $conditionalLogic;
        if ($logic['condition_type'] === 'show_when') {
            return function ($get) use ($logic, $prefix) {
                $dependentValue = $get($prefix . $logic['depends_on_field']);
                return in_array($dependentValue, $logic['trigger_values']);
            };
        }

        return true;
    }

    /**
     * Check if field should be visible based on data
     * 
     * @param object $field Field object
     * @param array $data Form data
     * @return bool
     */
    public static function isFieldVisible($field, array $data): bool
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

    /**
     * Check if condition is met
     * 
     * @param array $logic Conditional logic
     * @param mixed $value Current value
     * @return bool
     */
    public static function isConditionMet(array $logic, $value): bool
    {
        switch ($logic['condition_type']) {
            case 'show_when':
                return in_array($value, $logic['trigger_values']);
            case 'hide_when':
                return !in_array($value, $logic['trigger_values']);
            case 'equals':
                return $value === $logic['expected_value'];
            case 'not_equals':
                return $value !== $logic['expected_value'];
            default:
                return true;
        }
    }
}
