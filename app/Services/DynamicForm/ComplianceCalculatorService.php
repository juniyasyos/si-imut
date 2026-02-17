<?php

namespace App\Services\DynamicForm;

use App\Filament\Resources\ImutProfileResource\Pages\Helper\FormFields;
use App\Models\FormTemplate;

class ComplianceCalculatorService
{
    /**
     * Calculate compliance score based on form template and data
     */
    public static function calculateCompliance(FormTemplate $formTemplate, array $data): array
    {
        $totalWeight = 0;
        $totalScore = 0;
        $fieldBreakdown = [];
        $warnings = [];
        $autoFail = false;

        foreach ($formTemplate->formFields as $field) {
            $fieldValue = $data[$field->field_key] ?? null;
            $fieldScore = 0;

            // Skip if field is not visible due to conditional logic
            if ($field->conditional_logic && !FormFields::isFieldVisible($field, $data)) {
                continue;
            }

            if ($field->compliance_weight > 0 && $field->field_type !== 'text') {
                $totalWeight += $field->compliance_weight;

                // Check if field is filled (different logic for different field types)
                $isFilled = false;

                if ($field->field_type === 'time_duration' || $field->field_type === 'time_range') {
                    // For time_duration and time_range, consider filled if valid indicator is '1'
                    $validIndicator = $data[$field->field_key . '_valid_indicator'] ?? '0';
                    $isFilled = $validIndicator === '1';
                } else {
                    $isFilled = !empty($fieldValue);
                }

                // Calculate field score based on field type and options
                if ($isFilled) {
                    switch ($field->field_type) {
                        case 'single_select':
                            $option = $field->options->where('option_value', $fieldValue)->first();
                            if ($option) {
                                // Simple boolean: is_correct = Pass (full weight), not correct = Fail (0)
                                $fieldScore = ($option->is_correct ?? false) ? $field->compliance_weight : 0;
                            }
                            break;

                        case 'multi_select':
                            if (is_array($fieldValue)) {
                                $correctSelected = 0;
                                $wrongSelected = 0;

                                foreach ($fieldValue as $value) {
                                    $option = $field->options->where('option_value', $value)->first();
                                    if ($option) {
                                        if ($option->is_correct ?? false) {
                                            $correctSelected++;
                                        } else {
                                            $wrongSelected++;
                                        }
                                    }
                                }

                                // Apply boolean rules
                                $complianceRules = $field->compliance_rules ?? [];
                                $minCorrect = $complianceRules['minimum_correct'] ?? 1;
                                $allowWrong = $complianceRules['allow_wrong_selections'] ?? true;

                                // Check if passes rules
                                $passesMinimum = $correctSelected >= $minCorrect;
                                $passesWrongRule = $allowWrong || $wrongSelected == 0;

                                // dd([
                                //     'field_key' => $field->field_key,
                                //     'correctSelected' => $correctSelected,
                                //     'wrongSelected' => $wrongSelected,
                                //     'minCorrect' => $minCorrect,
                                //     'allowWrong' => $allowWrong,
                                //     'passesMinimum' => $passesMinimum,
                                //     'passesWrongRule' => $passesWrongRule,
                                //     '$minCorrect' => $minCorrect,
                                // ]);

                                $fieldScore = ($passesMinimum && $passesWrongRule) ? $field->compliance_weight : 0;
                            }
                            break;

                        case 'boolean':
                            if ($field->options->count() > 0) {
                                $option = $field->options->where('option_value', $fieldValue)->first();
                                if ($option) {
                                    $fieldScore = $option->compliance_value * $field->compliance_weight;
                                }
                            } else {
                                $fieldScore = $fieldValue ? $field->compliance_weight : 0;
                            }
                            break;

                        case 'time_duration':
                        case 'time_range':
                            // Score already set based on isFilled (valid indicator)
                            $fieldScore = $field->compliance_weight;
                            break;

                        case 'text':
                            // Text fields do not contribute to compliance score
                            $fieldScore = 0;
                            break;

                        default:
                            $fieldScore = $field->compliance_weight; // Default to full score if filled
                    }
                } else if ($field->is_critical_field) {
                    $validationConfig = is_array($field->validation_config) ? $field->validation_config : [];
                    if ($validationConfig['required'] ?? false) {
                        $warnings[] = "❌ Field kritis '{$field->field_label}' wajib diisi. Jawaban Anda tidak memenuhi standar compliance.";
                        if ($formTemplate->auto_fail_on_critical) {
                            $autoFail = true;
                        }
                    }
                }

                $totalScore += $fieldScore;
                $fieldBreakdown[$field->field_key] = [
                    'score' => $fieldScore,
                    'weight' => $field->field_type === 'text' ? 0 : $field->compliance_weight
                ];
            }
        }

        $percentage = $totalWeight > 0 ? ($totalScore / $totalWeight) * 100 : 0;

        if ($autoFail) {
            $percentage = 0;
            $status = '❌ TIDAK PATUH (Field kritis kosong)';
        } else {
            $status = $percentage >= 100 ? '✅ PATUH' : '❌ TIDAK PATUH';
        }

        return [
            'score' => $percentage,
            'total_score' => $totalScore,
            'total_weight' => $totalWeight,
            'status' => $status,
            'fields' => $fieldBreakdown,
            'warnings' => $warnings,
            'auto_fail' => $autoFail
        ];
    }

    /**
     * Calculate compliance and return formatted results for storage
     */
    public static function calculateForStorage(FormTemplate $formTemplate, array $data): array
    {
        $compliance = static::calculateCompliance($formTemplate, $data);

        return [
            'compliance_score' => $compliance['score'],
            'compliance_status' => $compliance['status'],
            'compliance_details' => [
                'total_score' => $compliance['total_score'],
                'total_weight' => $compliance['total_weight'],
                'field_breakdown' => $compliance['fields'],
                'warnings' => $compliance['warnings'],
                'auto_fail' => $compliance['auto_fail']
            ]
        ];
    }
}
