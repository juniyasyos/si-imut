<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a numeric value is within acceptable quality range
 */
class QualityRange implements ValidationRule
{
    protected float $min;
    protected float $max;
    protected string $type;

    public function __construct(float $min = 0, float $max = 100, string $type = 'percentage')
    {
        $this->min = $min;
        $this->max = $max;
        $this->type = $type;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value)) {
            $fail("Field {$attribute} harus berupa angka.");
            return;
        }

        $numericValue = (float) $value;

        if ($numericValue < $this->min || $numericValue > $this->max) {
            $fail("Field {$attribute} harus berada dalam rentang {$this->min} - {$this->max}.");
        }

        // Additional validation based on type
        switch ($this->type) {
            case 'percentage':
                if ($numericValue < 0 || $numericValue > 100) {
                    $fail("Field {$attribute} harus berupa persentase yang valid (0-100).");
                }
                break;

            case 'rate':
                if ($numericValue < 0) {
                    $fail("Field {$attribute} tidak boleh bernilai negatif.");
                }
                break;

            case 'ratio':
                if ($numericValue < 0 || $numericValue > 1) {
                    $fail("Field {$attribute} harus berupa rasio yang valid (0-1).");
                }
                break;
        }
    }
}
