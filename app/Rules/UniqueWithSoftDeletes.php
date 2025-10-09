<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Custom validation rule for unique fields with soft deletes
 *
 * This rule ensures uniqueness while ignoring soft deleted records,
 * preventing conflicts when creating new records with the same value
 * as a previously soft-deleted record.
 */
class UniqueWithSoftDeletes implements ValidationRule
{
    protected string $table;
    protected string $column;
    protected ?int $ignoreId;
    protected ?string $idColumn;

    /**
     * Create a new rule instance.
     *
     * @param string $table The database table name
     * @param string $column The column to check for uniqueness
     * @param int|null $ignoreId ID to ignore (for updates)
     * @param string $idColumn The primary key column name
     */
    public function __construct(
        string $table,
        string $column,
        ?int $ignoreId = null,
        string $idColumn = 'id'
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->idColumn = $idColumn;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->whereNull('deleted_at');

        // Ignore current record when updating
        if ($this->ignoreId) {
            $query->where($this->idColumn, '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("The {$attribute} has already been taken.");
        }
    }

    /**
     * Static helper method for easier usage
     */
    public static function for(string $table, string $column, ?int $ignoreId = null): self
    {
        return new self($table, $column, $ignoreId);
    }
}
