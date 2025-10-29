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
        Schema::table('imut_benchmarkings', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('imut_benchmarkings', 'period_start')) {
                $table->date('period_start')->nullable()
                    ->comment('Tanggal mulai benchmarking berlaku');
            }

            if (!Schema::hasColumn('imut_benchmarkings', 'period_end')) {
                $table->date('period_end')->nullable()
                    ->comment('Tanggal akhir benchmarking berlaku (null = berlaku selamanya)');
            }

            if (!Schema::hasColumn('imut_benchmarkings', 'is_active')) {
                $table->boolean('is_active')->default(true)
                    ->comment('Status aktif benchmarking');
            }

            if (!Schema::hasColumn('imut_benchmarkings', 'notes')) {
                $table->text('notes')->nullable()
                    ->comment('Catatan atau alasan perubahan benchmarking');
            }

            if (!Schema::hasColumn('imut_benchmarkings', 'created_by')) {
                $table->foreignId('created_by')->nullable()
                    ->constrained('users')->nullOnDelete()
                    ->comment('User yang membuat benchmarking');
            }

            if (!Schema::hasColumn('imut_benchmarkings', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()
                    ->constrained('users')->nullOnDelete()
                    ->comment('User yang terakhir update benchmarking');
            }
        });

        // Add indexes - Database agnostic way
        $this->addIndexesIfNotExist();
    }

    /**
     * Add indexes if they don't exist (database-agnostic)
     */
    private function addIndexesIfNotExist(): void
    {
        $existingIndexes = $this->getExistingIndexes();

        Schema::table('imut_benchmarkings', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('idx_benchmark_lookup', $existingIndexes)) {
                $table->index(['imut_data_id', 'year', 'month', 'is_active'], 'idx_benchmark_lookup');
            }

            if (!in_array('idx_benchmark_period', $existingIndexes)) {
                $table->index(['region_type_id', 'period_start', 'period_end'], 'idx_benchmark_period');
            }

            if (!in_array('idx_benchmark_unique_check', $existingIndexes)) {
                $table->index(['imut_data_id', 'region_type_id', 'year', 'month'], 'idx_benchmark_unique_check');
            }

            if (!in_array('unique_benchmark_period', $existingIndexes)) {
                $table->unique(
                    ['imut_data_id', 'region_type_id', 'year', 'month'],
                    'unique_benchmark_period'
                );
            }
        });
    }

    /**
     * Get existing indexes in a database-agnostic way
     */
    private function getExistingIndexes(): array
    {
        $driver = DB::getDriverName();

        try {
            switch ($driver) {
                case 'mysql':
                    $indexes = DB::select("SHOW INDEX FROM imut_benchmarkings");
                    return collect($indexes)->pluck('Key_name')->unique()->toArray();

                case 'pgsql':
                    $indexes = DB::select("
                        SELECT indexname
                        FROM pg_indexes
                        WHERE tablename = 'imut_benchmarkings'
                    ");
                    return collect($indexes)->pluck('indexname')->unique()->toArray();

                case 'sqlite':
                    $indexes = DB::select("
                        SELECT name
                        FROM sqlite_master
                        WHERE type = 'index'
                        AND tbl_name = 'imut_benchmarkings'
                    ");
                    return collect($indexes)->pluck('name')->unique()->toArray();

                case 'sqlsrv':
                    $indexes = DB::select("
                        SELECT i.name
                        FROM sys.indexes i
                        INNER JOIN sys.tables t ON i.object_id = t.object_id
                        WHERE t.name = 'imut_benchmarkings'
                    ");
                    return collect($indexes)->pluck('name')->unique()->toArray();

                default:
                    // Fallback: return empty array to create all indexes
                    return [];
            }
        } catch (\Exception $e) {
            // If error, return empty array to attempt creating all indexes
            // Laravel will handle duplicate index errors gracefully
            return [];
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_benchmarkings', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('unique_benchmark_period');

            // Drop indexes
            $table->dropIndex('idx_benchmark_lookup');
            $table->dropIndex('idx_benchmark_period');
            $table->dropIndex('idx_benchmark_unique_check');

            // Drop foreign keys first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

            // Drop columns
            $table->dropColumn([
                'period_start',
                'period_end',
                'is_active',
                'notes',
                'created_by',
                'updated_by',
            ]);
        });
    }
};
