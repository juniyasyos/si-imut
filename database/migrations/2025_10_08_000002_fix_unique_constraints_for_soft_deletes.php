<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fix imut_kategori table - category_name unique constraint with soft deletes
        $this->fixUniqueConstraintForSoftDeletes('imut_kategori', 'category_name');

        // 2. Fix imut_data table - title unique constraint with soft deletes
        $this->fixUniqueConstraintForSoftDeletes('imut_data', 'title');

        // 3. Fix imut_profil table - slug unique constraint with soft deletes
        $this->fixUniqueConstraintForSoftDeletes('imut_profil', 'slug');

        // 4. Fix unit_kerja table - unit_name unique constraint with soft deletes
        $this->fixUniqueConstraintForSoftDeletes('unit_kerja', 'unit_name');

        // 5. Fix users table - nik and email unique constraints with soft deletes
        $this->fixUniqueConstraintForSoftDeletes('users', 'nik');
        $this->fixUniqueConstraintForSoftDeletes('users', 'email');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original unique constraints (without soft delete consideration)
        $this->restoreOriginalUniqueConstraint('imut_kategori', 'category_name');
        $this->restoreOriginalUniqueConstraint('imut_data', 'title');
        $this->restoreOriginalUniqueConstraint('imut_profil', 'slug');
        $this->restoreOriginalUniqueConstraint('unit_kerja', 'unit_name');
        $this->restoreOriginalUniqueConstraint('users', 'nik');
        $this->restoreOriginalUniqueConstraint('users', 'email');
    }

    /**
     * Fix unique constraint to work properly with soft deletes
     */
    private function fixUniqueConstraintForSoftDeletes(string $table, string $column): void
    {
        try {
            // Drop existing unique constraint if it exists
            $indexes = Schema::getIndexes($table);
            foreach ($indexes as $index) {
                if (in_array($column, $index['columns']) && $index['unique']) {
                    Schema::table($table, function (Blueprint $table) use ($index) {
                        $table->dropUnique($index['name']);
                    });
                    break;
                }
            }

            // Create partial unique index that excludes soft deleted records
            $indexName = "unique_{$table}_{$column}_not_deleted";

            // Use raw SQL for partial unique index (MySQL 8.0+ supports this)
            if (DB::getDriverName() === 'mysql') {
                $sql = "CREATE UNIQUE INDEX `{$indexName}` ON `{$table}` (`{$column}`) WHERE `deleted_at` IS NULL";
                DB::statement($sql);
            } else {
                // For other databases, create composite unique index with deleted_at
                Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                    $table->unique([$column, 'deleted_at'], $indexName);
                });
            }

        } catch (\Exception $e) {
            // Log error but don't fail migration for non-critical constraint fixes
            logger()->warning("Failed to fix unique constraint for {$table}.{$column}: " . $e->getMessage());
        }
    }

    /**
     * Restore original unique constraint
     */
    private function restoreOriginalUniqueConstraint(string $table, string $column): void
    {
        try {
            // Drop the soft-delete-aware unique constraint
            $indexName = "unique_{$table}_{$column}_not_deleted";

            if (DB::getDriverName() === 'mysql') {
                DB::statement("DROP INDEX IF EXISTS `{$indexName}` ON `{$table}`");
            } else {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropUnique($indexName);
                });
            }

            // Restore original simple unique constraint
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->unique($column);
            });

        } catch (\Exception $e) {
            logger()->warning("Failed to restore unique constraint for {$table}.{$column}: " . $e->getMessage());
        }
    }
};
