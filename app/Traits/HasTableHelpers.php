<?php

namespace App\Traits;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Trait HasTableHelpers
 *
 * Provides common table helper methods for Filament tables.
 *
 * @package App\Traits
 */
trait HasTableHelpers
{
    /**
     * Create a searchable text column with custom database column
     *
     * @param string $name Column name
     * @param string $label Display label
     * @param string $dbColumn Database column path for search query
     * @return TextColumn
     */
    protected function makeSearchableColumn(string $name, string $label, string $dbColumn): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->toggleable()
            ->limit(80)
            ->searchable(
                query: fn(EloquentBuilder $query, string $search) => $query->where($dbColumn, 'like', "%{$search}%")
            );
    }
}
