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
     * @param mixed $conditionalLogic Conditional logic configuration (array, object, or JSON string)
     * @param string $prefix Field prefix
     * @return bool|callable Visibility condition
     */
    public static function getVisibilityCondition($conditionalLogic, string $prefix = '')
    {
        if (!$conditionalLogic) {
            return true;
        }

        // Handle if conditional_logic is stored as JSON string
        if (is_string($conditionalLogic)) {
            $conditionalLogic = json_decode($conditionalLogic, true);
            if (!is_array($conditionalLogic)) {
                return true; // Invalid JSON or not an array
            }
        }

        // Ensure it's an array
        if (!is_array($conditionalLogic)) {
            return true;
        }

        $logic = $conditionalLogic;
        if (isset($logic['condition_type']) && $logic['condition_type'] === 'show_when') {
            return function ($get) use ($logic, $prefix) {
                $dependentValue = $get($prefix . $logic['depends_on_field']);
                return isset($logic['trigger_values']) && is_array($logic['trigger_values'])
                    ? in_array($dependentValue, $logic['trigger_values'])
                    : false;
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

        $conditionalLogic = $field->conditional_logic;

        // Handle if conditional_logic is stored as JSON string
        if (is_string($conditionalLogic)) {
            $conditionalLogic = json_decode($conditionalLogic, true);
            if (!is_array($conditionalLogic)) {
                return true; // Invalid JSON or not an array
            }
        }

        // Ensure it's an array
        if (!is_array($conditionalLogic)) {
            return true;
        }

        $logic = $conditionalLogic;
        $dependentValue = $data[$logic['depends_on_field']] ?? null;

        if (isset($logic['condition_type']) && $logic['condition_type'] === 'show_when') {
            return isset($logic['trigger_values']) && is_array($logic['trigger_values'])
                ? in_array($dependentValue, $logic['trigger_values'])
                : false;
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
