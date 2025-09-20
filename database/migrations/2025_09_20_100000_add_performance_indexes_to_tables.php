<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add performance indexes based on query patterns analysis
     */
    public function up(): void
    {
        // 1. Add composite indexes for frequently queried combinations
        Schema::table('laporan_imuts', function (Blueprint $table) {
            // For date range queries and status filtering
            $table->index(['assessment_period_start', 'assessment_period_end'], 'idx_laporan_imuts_period_range');
            $table->index(['status', 'assessment_period_start'], 'idx_laporan_imuts_status_start');
            $table->index(['created_by', 'assessment_period_start'], 'idx_laporan_imuts_creator_start');

            // For yearly reports with status filtering
            $table->index(['status', 'created_at'], 'idx_laporan_imuts_status_created');
        });

        // 2. Add indexes for ImutPenilaian frequent queries
        Schema::table('imut_penilaians', function (Blueprint $table) {
            // For null value checks (incomplete assessments)
            $table->index(['numerator_value', 'denominator_value'], 'idx_imut_penilaians_values');

            // For profile-based queries with null checks
            $table->index(['imut_profil_id', 'numerator_value'], 'idx_imut_penilaians_profil_numerator');
            $table->index(['imut_profil_id', 'denominator_value'], 'idx_imut_penilaians_profil_denominator');
        });

        // 3. Add indexes for ImutData queries
        Schema::table('imut_data', function (Blueprint $table) {
            // For category and status filtering
            $table->index(['imut_kategori_id', 'status'], 'idx_imut_data_category_status');

            // For creator-based queries
            $table->index(['created_by', 'status'], 'idx_imut_data_creator_status');

            // For slug-based lookups with status
            $table->index(['slug', 'status'], 'idx_imut_data_slug_status');

            // For soft delete aware queries
            $table->index(['status', 'deleted_at'], 'idx_imut_data_status_deleted');
        });

        // 4. Add indexes for Unit Kerja relationships
        Schema::table('unit_kerjas', function (Blueprint $table) {
            // For hierarchical queries (parent-child relationships)
            if (Schema::hasColumn('unit_kerjas', 'parent_id')) {
                $table->index(['parent_id', 'is_active'], 'idx_unit_kerjas_parent_active');
            }

            // For region-based queries if region columns exist
            if (Schema::hasColumn('unit_kerjas', 'region_id')) {
                $table->index(['region_id', 'is_active'], 'idx_unit_kerjas_region_active');
            }
        });

        // 5. Add indexes for ImutProfile version queries
        Schema::table('imut_profil', function (Blueprint $table) {
            // For latest version queries (latestOfMany)
            $table->index(['imut_data_id', 'version', 'created_at'], 'idx_imut_profil_latest_version');

            // For active profile queries
            if (Schema::hasColumn('imut_profil', 'is_active')) {
                $table->index(['imut_data_id', 'is_active', 'version'], 'idx_imut_profil_active_version');
            }
        });

        // 6. Add indexes for User-related queries
        Schema::table('users', function (Blueprint $table) {
            // For email verification and active user queries
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->index(['email_verified_at', 'created_at'], 'idx_users_verified_created');
            }

            // For role-based queries if using Spatie Permission
            if (Schema::hasTable('model_has_roles')) {
                // This will be handled separately for pivot table
            }
        });

        // 7. Add indexes for Activity Log (Spatie)
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                // For subject-based activity queries
                $table->index(['subject_type', 'subject_id', 'created_at'], 'idx_activity_log_subject_created');

                // For causer-based activity queries
                $table->index(['causer_type', 'causer_id', 'created_at'], 'idx_activity_log_causer_created');

                // For event-based queries
                $table->index(['event', 'created_at'], 'idx_activity_log_event_created');
            });
        }

        // 8. Add indexes for Cache tables if using database cache
        if (Schema::hasTable('cache')) {
            Schema::table('cache', function (Blueprint $table) {
                // Expiration-based cleanup queries
                $table->index('expiration', 'idx_cache_expiration');
            });
        }

        // 9. Add indexes for Jobs table
        if (Schema::hasTable('jobs')) {
            Schema::table('jobs', function (Blueprint $table) {
                // For job processing optimization - only on non-TEXT columns
                $table->index('available_at', 'idx_jobs_available');
                $table->index('reserved_at', 'idx_jobs_reserved');
            });
        }

        // 10. Add indexes for Failed Jobs
        if (Schema::hasTable('failed_jobs')) {
            Schema::table('failed_jobs', function (Blueprint $table) {
                // For failed job analysis - only on date column
                $table->index('failed_at', 'idx_failed_jobs_failed');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all indexes created in up() method

        Schema::table('laporan_imuts', function (Blueprint $table) {
            $table->dropIndex('idx_laporan_imuts_period_range');
            $table->dropIndex('idx_laporan_imuts_status_start');
            $table->dropIndex('idx_laporan_imuts_creator_start');
            $table->dropIndex('idx_laporan_imuts_status_created');
        });

        Schema::table('imut_penilaians', function (Blueprint $table) {
            $table->dropIndex('idx_imut_penilaians_values');
            $table->dropIndex('idx_imut_penilaians_profil_numerator');
            $table->dropIndex('idx_imut_penilaians_profil_denominator');
        });

        Schema::table('imut_data', function (Blueprint $table) {
            $table->dropIndex('idx_imut_data_category_status');
            $table->dropIndex('idx_imut_data_creator_status');
            $table->dropIndex('idx_imut_data_slug_status');
            $table->dropIndex('idx_imut_data_status_deleted');
        });

        if (Schema::hasTable('unit_kerjas')) {
            Schema::table('unit_kerjas', function (Blueprint $table) {
                if (Schema::hasColumn('unit_kerjas', 'parent_id')) {
                    $table->dropIndex('idx_unit_kerjas_parent_active');
                }
                if (Schema::hasColumn('unit_kerjas', 'region_id')) {
                    $table->dropIndex('idx_unit_kerjas_region_active');
                }
            });
        }

        Schema::table('imut_profil', function (Blueprint $table) {
            $table->dropIndex('idx_imut_profil_latest_version');
            if (Schema::hasColumn('imut_profil', 'is_active')) {
                $table->dropIndex('idx_imut_profil_active_version');
            }
        });

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'email_verified_at')) {
                    $table->dropIndex('idx_users_verified_created');
                }
            });
        }

        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropIndex('idx_activity_log_subject_created');
                $table->dropIndex('idx_activity_log_causer_created');
                $table->dropIndex('idx_activity_log_event_created');
            });
        }

        if (Schema::hasTable('cache')) {
            Schema::table('cache', function (Blueprint $table) {
                $table->dropIndex('idx_cache_expiration');
            });
        }

        if (Schema::hasTable('jobs')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropIndex('idx_jobs_available');
                $table->dropIndex('idx_jobs_reserved');
            });
        }

        if (Schema::hasTable('failed_jobs')) {
            Schema::table('failed_jobs', function (Blueprint $table) {
                $table->dropIndex('idx_failed_jobs_failed');
            });
        }
    }
};
