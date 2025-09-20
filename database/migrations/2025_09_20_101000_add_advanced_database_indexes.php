<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add database optimizations for better performance
     */
    public function up(): void
    {
        // 1. Create partial indexes for frequently filtered data
        if (Schema::hasTable('imut_data')) {
            // Using raw SQL for partial indexes (MySQL/PostgreSQL specific)
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                // MySQL doesn't support partial indexes, but we can optimize with functional indexes
                DB::statement('CREATE INDEX idx_imut_data_active_only ON imut_data (id) WHERE status = 1');
            } elseif ($driver === 'pgsql') {
                // PostgreSQL supports partial indexes
                DB::statement('CREATE INDEX idx_imut_data_active_only ON imut_data (id) WHERE status = true');
                DB::statement('CREATE INDEX idx_imut_data_deleted_only ON imut_data (id) WHERE deleted_at IS NOT NULL');
            }
        }

        // 2. Create covering indexes for common SELECT patterns
        if (Schema::hasTable('laporan_imuts')) {
            Schema::table('laporan_imuts', function (Blueprint $table) {
                // Covering index for dashboard queries
                $table->index(['id', 'name', 'status', 'assessment_period_start', 'assessment_period_end'], 'idx_laporan_imuts_dashboard_cover');
            });
        }

        // 3. Add indexes for foreign key relationships not yet covered
        if (Schema::hasTable('imut_data_unit_kerja')) {
            Schema::table('imut_data_unit_kerja', function (Blueprint $table) {
                // For relationship queries
                $table->index(['unit_kerja_id', 'assigned_at'], 'idx_imut_data_unit_kerja_assigned');
                $table->index(['assigned_by', 'assigned_at'], 'idx_imut_data_unit_kerja_assigner');
            });
        }

        // 4. Add indexes for pivot tables
        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                // For permission checking queries
                $table->index(['model_type', 'model_id', 'permission_id'], 'idx_model_permissions_lookup');
            });
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                // For role checking queries
                $table->index(['model_type', 'model_id', 'role_id'], 'idx_model_roles_lookup');
            });
        }

        // 5. Add text search indexes for content search
        if (Schema::hasTable('imut_data')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                // MySQL FULLTEXT index for search
                DB::statement('CREATE FULLTEXT INDEX idx_imut_data_fulltext ON imut_data (title, description)');
            } elseif ($driver === 'pgsql') {
                // PostgreSQL GIN index for text search
                DB::statement('CREATE INDEX idx_imut_data_gin_search ON imut_data USING gin(to_tsvector(\'indonesian\', title || \' \' || description))');
            }
        }

        // 6. Add hash indexes for exact lookups (PostgreSQL)
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            if (Schema::hasTable('users')) {
                DB::statement('CREATE INDEX idx_users_email_hash ON users USING hash(email)');
            }

            if (Schema::hasTable('imut_data')) {
                DB::statement('CREATE INDEX idx_imut_data_slug_hash ON imut_data USING hash(slug)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Drop partial indexes
        if ($driver === 'mysql') {
            DB::statement('DROP INDEX IF EXISTS idx_imut_data_active_only ON imut_data');
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_imut_data_active_only');
            DB::statement('DROP INDEX IF EXISTS idx_imut_data_deleted_only');
        }

        // Drop covering indexes
        if (Schema::hasTable('laporan_imuts')) {
            Schema::table('laporan_imuts', function (Blueprint $table) {
                $table->dropIndex('idx_laporan_imuts_dashboard_cover');
            });
        }

        // Drop pivot table indexes
        if (Schema::hasTable('imut_data_unit_kerja')) {
            Schema::table('imut_data_unit_kerja', function (Blueprint $table) {
                $table->dropIndex('idx_imut_data_unit_kerja_assigned');
                $table->dropIndex('idx_imut_data_unit_kerja_assigner');
            });
        }

        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropIndex('idx_model_permissions_lookup');
            });
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropIndex('idx_model_roles_lookup');
            });
        }

        // Drop text search indexes
        if ($driver === 'mysql') {
            DB::statement('DROP INDEX IF EXISTS idx_imut_data_fulltext ON imut_data');
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_imut_data_gin_search');
            DB::statement('DROP INDEX IF EXISTS idx_users_email_hash');
            DB::statement('DROP INDEX IF EXISTS idx_imut_data_slug_hash');
        }
    }
};
