<?php

namespace App\Traits;

use App\Rules\UniqueWithSoftDeletes;
use Illuminate\Validation\Rule;

/**
 * Trait for models that need unique validation with soft deletes
 *
 * This trait provides helper methods to create validation rules
 * that work properly with soft deleted records.
 */
trait HasUniqueWithSoftDeletes
{
    /**
     * Get unique validation rule that ignores soft deleted records
     *
     * @param string $column The column to validate
     * @param int|null $ignoreId ID to ignore (for updates)
     * @return UniqueWithSoftDeletes
     */
    public function uniqueRule(string $column, ?int $ignoreId = null): UniqueWithSoftDeletes
    {
        return UniqueWithSoftDeletes::for($this->getTable(), $column, $ignoreId);
    }

    /**
     * Get unique validation rule for current model instance (for updates)
     *
     * @param string $column The column to validate
     * @return UniqueWithSoftDeletes
     */
    public function uniqueRuleForUpdate(string $column): UniqueWithSoftDeletes
    {
        return $this->uniqueRule($column, $this->getKey());
    }

    /**
     * Check if a value is unique (ignoring soft deleted records)
     *
     * @param string $column The column to check
     * @param mixed $value The value to check
     * @param int|null $ignoreId ID to ignore
     * @return bool
     */
    public function isUniqueValue(string $column, mixed $value, ?int $ignoreId = null): bool
    {
        $query = static::where($column, $value);

        if ($ignoreId) {
            $query->where($this->getKeyName(), '!=', $ignoreId);
        }

        return !$query->exists();
    }

    /**
     * Get validation rules for unique fields
     * Override this method in your model to define which fields should be unique
     *
     * @param int|null $ignoreId
     * @return array
     */
    public function getUniqueValidationRules(?int $ignoreId = null): array
    {
        return [];
    }

    /**
     * Boot the trait
     */
    protected static function bootHasUniqueWithSoftDeletes(): void
    {
        // Add model validation before saving
        static::saving(function ($model) {
            if (method_exists($model, 'validateUniqueFields')) {
                $model->validateUniqueFields();
            }
        });
    }
}
