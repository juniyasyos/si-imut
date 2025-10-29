<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * OPTIMIZATION: Remove year/month redundancy.
     * Use only period_start/period_end for maximum flexibility.
     *
     * Benefits:
     * - No contradiction between year/month and period dates
     * - Support for any date range (not just monthly)
     * - Simpler schema
     * - Can extract year/month from period_start when needed
     */
    public function up(): void
    {
        Schema::table('imut_benchmarkings', function (Blueprint $table) {
            // Check and add period validity columns if not exists
            if (!Schema::hasColumn('imut_benchmarkings', 'period_start')) {
                $table->date('period_start')->nullable()->after('benchmark_value');
            }

            if (!Schema::hasColumn('imut_benchmarkings', 'period_end')) {
                $table->date('period_end')->nullable()->after('period_start');
            }

            // Check and add audit trail if not exists
            if (!Schema::hasColumn('imut_benchmarkings', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('period_end')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('imut_benchmarkings', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }

            // Check and add status and notes if not exists
            if (!Schema::hasColumn('imut_benchmarkings', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('updated_by');
            }

            if (!Schema::hasColumn('imut_benchmarkings', 'notes')) {
                $table->text('notes')->nullable()->after('is_active');
            }
        });

        // Add indexes (Laravel will handle if exists)
        try {
            Schema::table('imut_benchmarkings', function (Blueprint $table) {
                $table->index(['imut_data_id', 'region_type_id', 'period_start'], 'idx_benchmark_lookup');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('imut_benchmarkings', function (Blueprint $table) {
                $table->index('is_active', 'idx_benchmark_active');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('imut_benchmarkings', function (Blueprint $table) {
                $table->index(['period_start', 'period_end'], 'idx_benchmark_period');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        // Migrate existing data: convert year/month to period_start (only if year/month columns exist)
        if (Schema::hasColumn('imut_benchmarkings', 'year') && Schema::hasColumn('imut_benchmarkings', 'month')) {
            DB::statement("
                UPDATE imut_benchmarkings
                SET period_start = CONCAT(year, '-', LPAD(month, 2, '0'), '-01')
                WHERE period_start IS NULL AND year IS NOT NULL AND month IS NOT NULL
            ");

            // Set period_end to end of month for existing data
            DB::statement("
                UPDATE imut_benchmarkings
                SET period_end = LAST_DAY(period_start)
                WHERE period_start IS NOT NULL AND period_end IS NULL
            ");

            // Now make period_start required
            Schema::table('imut_benchmarkings', function (Blueprint $table) {
                $table->date('period_start')->nullable(false)->change();
            });

            // Finally, drop redundant year and month columns
            Schema::table('imut_benchmarkings', function (Blueprint $table) {
                // Drop unique constraint if exists
                try {
                    $table->dropUnique('unique_benchmark_period');
                } catch (\Exception $e) {
                    // Constraint might not exist
                }

                $table->dropColumn(['year', 'month']);
            });
        } else {
            // If year/month don't exist, just make sure period_start is required
            Schema::table('imut_benchmarkings', function (Blueprint $table) {
                $table->date('period_start')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_benchmarkings', function (Blueprint $table) {
            // Restore year and month columns
            $table->year('year')->after('region_name');
            $table->tinyInteger('month')->after('year');
        });

        // Migrate data back: extract year/month from period_start
        DB::statement("
            UPDATE imut_benchmarkings
            SET year = YEAR(period_start),
                month = MONTH(period_start)
            WHERE period_start IS NOT NULL
        ");

        // Drop new columns
        Schema::table('imut_benchmarkings', function (Blueprint $table) {
            $table->dropIndex('idx_benchmark_lookup');
            $table->dropIndex('idx_benchmark_active');
            $table->dropIndex('idx_benchmark_period');

            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

            $table->dropColumn([
                'period_start',
                'period_end',
                'created_by',
                'updated_by',
                'is_active',
                'notes'
            ]);
        });
    }
};
