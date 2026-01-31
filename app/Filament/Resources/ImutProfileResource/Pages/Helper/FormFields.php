<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper;

use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TextFieldBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\NumberFieldBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\SelectFieldBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\BooleanFieldBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeDurationFieldBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeRangeFieldBuilder;

/**
 * Main orchestrator for creating dynamic form components
 */
class FormFields
{
    /**
     * Create form component based on field configuration
     * 
     * @param object $field Field configuration
     * @param string $prefix Field prefix
     * @return mixed Form component
     */
    public static function createFormComponent($field, $prefix = '')
    {
        $fieldKey = $prefix . $field->field_key;
        $label = $field->field_label;
        $helperText = $field->field_description;
        $required = $field->validation_config['required'] ?? false;
        $visibleCondition = ConditionalLogicHandler::getVisibilityCondition($field->conditional_logic, $prefix);

        switch ($field->field_type) {
            case 'text':
                return TextFieldBuilder::create(
                    $fieldKey,
                    $label,
                    $helperText,
                    $field->validation_config['max_length'] ?? 255,
                    $required,
                    $visibleCondition
                );

            case 'number':
                return NumberFieldBuilder::create(
                    $fieldKey,
                    $label,
                    $helperText,
                    $field->validation_config['min'] ?? null,
                    $field->validation_config['max'] ?? null,
                    $required,
                    $visibleCondition
                );

            case 'single_select':
                $options = SelectFieldBuilder::extractOptions($field->options);
                return SelectFieldBuilder::createSingleSelect(
                    $fieldKey,
                    $label,
                    $helperText,
                    $options,
                    $required,
                    $visibleCondition
                );

            case 'multi_select':
                $options = SelectFieldBuilder::extractOptions($field->options);
                return SelectFieldBuilder::createMultiSelect(
                    $fieldKey,
                    $label,
                    $helperText,
                    $options,
                    $required,
                    $visibleCondition
                );

            case 'boolean':
                $options = SelectFieldBuilder::extractOptions($field->options);
                return BooleanFieldBuilder::create(
                    $fieldKey,
                    $label,
                    $helperText,
                    $options,
                    $required,
                    $visibleCondition
                );

            case 'time_duration':
                $threshold = $field->validation_config['threshold'] ?? '00:15';
                $thresholdType = $field->validation_config['threshold_type'] ?? 'less_than';
                // dd([
                //     'field_key' => $fieldKey,
                //     'field_validation_config' => $field->validation_config,
                //     'threshold_from_config' => $threshold,
                //     'threshold_type_from_config' => $thresholdType,
                //     'field_object' => $field
                // ]);
                return TimeDurationFieldBuilder::create(
                    $fieldKey,
                    $required,
                    $visibleCondition,
                    $threshold,
                    $thresholdType
                );

            case 'time_range':
                $defaultStartTime = $field->validation_config['default_start_time'] ?? '08:00';
                $defaultEndTime = $field->validation_config['default_end_time'] ?? '17:00';
                return TimeRangeFieldBuilder::create(
                    $fieldKey,
                    $required,
                    $visibleCondition,
                    $defaultStartTime,
                    $defaultEndTime
                );

            default:
                return TextFieldBuilder::create(
                    $fieldKey,
                    $label,
                    $helperText,
                    255,
                    $required,
                    $visibleCondition
                );
        }
    }

    /**
     * Check if field should be visible based on data
     * 
     * @param object $field Field configuration
     * @param array $data Form data
     * @return bool
     */
    public static function isFieldVisible($field, $data): bool
    {
        return ConditionalLogicHandler::isFieldVisible($field, $data);
    }

    // ========================================
    // Legacy Support Methods (Deprecated)
    // These methods are kept for backward compatibility
    // Use TimeUtility and TimeDurationFieldBuilder classes directly instead
    // ========================================

    /**
     * @deprecated Use TimeDurationFieldBuilder::create() instead
     */
    public static function createTimeDurationField(
        string $fieldKey,
        bool $required = false,
        $visibleCondition = true,
        string $defaultThreshold = '08:00'
    ) {
        return TimeDurationFieldBuilder::create($fieldKey, $required, $visibleCondition, $defaultThreshold);
    }

    /**
     * @deprecated Use TimeDurationFieldBuilder::createStartTimePicker() instead
     */
    private static function createStartTimePicker(string $fieldKey, bool $required)
    {
        return TimeDurationFieldBuilder::createStartTimePicker($fieldKey, $required);
    }

    /**
     * @deprecated Use TimeDurationFieldBuilder::createEndTimePicker() instead
     */
    private static function createEndTimePicker(string $fieldKey, bool $required)
    {
        return TimeDurationFieldBuilder::createEndTimePicker($fieldKey, $required);
    }

    /**
     * @deprecated Use TimeDurationFieldBuilder::createThresholdPicker() instead
     */
    private static function createThresholdPicker(string $fieldKey, string $defaultValue)
    {
        return TimeDurationFieldBuilder::createThresholdPicker($fieldKey, $defaultValue);
    }

    /**
     * @deprecated Use TimeDurationFieldBuilder::createValidationIndicator() instead
     */
    private static function createValidationIndicator(string $fieldKey)
    {
        return TimeDurationFieldBuilder::createValidationIndicator($fieldKey);
    }

    /**
     * @deprecated Use TimeDurationFieldBuilder::validateDurationAndSetIndicator() instead
     */
    public static function validateDurationAndSetIndicator(callable $get, callable $set, string $fieldKey): void
    {
        TimeDurationFieldBuilder::validateDurationAndSetIndicator($get, $set, $fieldKey);
    }

    /**
     * @deprecated Use TimeDurationFieldBuilder::isDurationValid() instead
     */
    public static function isDurationValid(callable $get, string $fieldKey): bool
    {
        return TimeDurationFieldBuilder::isDurationValid($get, $fieldKey);
    }

    /**
     * @deprecated Use TimeUtility::checkDurationValidity() instead
     */
    public static function checkDurationValidity(?string $startTime, ?string $endTime, string $thresholdTime = '08:00', string $thresholdType = 'less_than'): bool
    {
        return TimeUtility::checkDurationValidity($startTime, $endTime, $thresholdTime, $thresholdType);
    }

    /**
     * @deprecated Use TimeUtility::calculateDurationInMinutes() instead
     */
    public static function calculateDurationInMinutes(string $startTime, string $endTime): ?int
    {
        return TimeUtility::calculateDurationInMinutes($startTime, $endTime);
    }

    /**
     * @deprecated Use TimeUtility::convertTimeToMinutes() instead
     */
    public static function convertTimeToMinutes(string $time): int
    {
        return TimeUtility::convertTimeToMinutes($time);
    }

    /**
     * @deprecated Use TimeUtility::convertMinutesToTime() instead
     */
    public static function convertMinutesToTime(int $minutes): string
    {
        return TimeUtility::convertMinutesToTime($minutes);
    }
}
