<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates mathematical formula syntax
 */
class ValidFormula implements ValidationRule
{
    protected array $allowedVariables;
    protected array $allowedOperators;

    public function __construct(array $allowedVariables = ['numerator', 'denominator'], array $allowedOperators = ['+', '-', '*', '/', '(', ')'])
    {
        $this->allowedVariables = $allowedVariables;
        $this->allowedOperators = $allowedOperators;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || empty(trim($value))) {
            $fail("Field {$attribute} harus berupa formula yang valid.");
            return;
        }

        $formula = trim($value);

        // Check for basic formula structure
        if (!$this->hasValidStructure($formula)) {
            $fail("Field {$attribute} tidak memiliki struktur formula yang valid.");
            return;
        }

        // Check for balanced parentheses
        if (!$this->hasBalancedParentheses($formula)) {
            $fail("Field {$attribute} memiliki tanda kurung yang tidak seimbang.");
            return;
        }

        // Check for valid characters
        if (!$this->hasValidCharacters($formula)) {
            $fail("Field {$attribute} mengandung karakter yang tidak diizinkan.");
            return;
        }

        // Check for valid variables
        if (!$this->hasValidVariables($formula)) {
            $fail("Field {$attribute} mengandung variabel yang tidak diizinkan. Variabel yang diizinkan: " . implode(', ', $this->allowedVariables));
        }
    }

    /**
     * Check if formula has valid basic structure
     */
    protected function hasValidStructure(string $formula): bool
    {
        // Must contain at least one variable or number
        return preg_match('/[a-zA-Z0-9]/', $formula);
    }

    /**
     * Check if parentheses are balanced
     */
    protected function hasBalancedParentheses(string $formula): bool
    {
        $count = 0;
        $chars = str_split($formula);

        foreach ($chars as $char) {
            if ($char === '(') {
                $count++;
            } elseif ($char === ')') {
                $count--;
                if ($count < 0) {
                    return false;
                }
            }
        }

        return $count === 0;
    }

    /**
     * Check if formula contains only valid characters
     */
    protected function hasValidCharacters(string $formula): bool
    {
        $allowedPattern = '/^[a-zA-Z0-9\s\+\-\*\/\(\)\.\_]+$/';
        return preg_match($allowedPattern, $formula);
    }

    /**
     * Check if all variables in formula are allowed
     */
    protected function hasValidVariables(string $formula): bool
    {
        // Extract variables (sequences of letters and underscores)
        preg_match_all('/[a-zA-Z_][a-zA-Z0-9_]*/', $formula, $matches);
        $variables = $matches[0];

        foreach ($variables as $variable) {
            if (!in_array($variable, $this->allowedVariables)) {
                return false;
            }
        }

        return true;
    }
}
