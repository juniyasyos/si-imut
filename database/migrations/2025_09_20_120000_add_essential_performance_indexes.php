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
     * Add essential performance optimizations
     */
    public function up(): void
    {
        // 1. Add missing indexes for ImutPenilaian null value checks
        if (Schema::hasTable('imut_penilaians')) {
            // Check if indexes don't exist before creating
            $this->createIndexIfNotExists('imut_penilaians', ['numerator_value', 'denominator_value'], 'idx_imut_penilaians_values');
        }

        // 2. Add missing indexes for ImutData category and status
        if (Schema::hasTable('imut_data')) {
            $this->createIndexIfNotExists('imut_data', ['imut_kategori_id', 'status'], 'idx_imut_data_category_status');
            $this->createIndexIfNotExists('imut_data', ['created_by', 'status'], 'idx_imut_data_creator_status');
            $this->createIndexIfNotExists('imut_data', ['slug', 'status'], 'idx_imut_data_slug_status');
        }

        // 3. Add indexes for ImutProfile version queries
        if (Schema::hasTable('imut_profil')) {
            $this->createIndexIfNotExists('imut_profil', ['imut_data_id', 'version', 'created_at'], 'idx_imut_profil_latest_version');
        }

        // 4. Add basic indexes for activity_log if exists
        if (Schema::hasTable('activity_log')) {
            $this->createIndexIfNotExists('activity_log', ['subject_type', 'subject_id'], 'idx_activity_log_subject');
            $this->createIndexIfNotExists('activity_log', ['causer_type', 'causer_id'], 'idx_activity_log_causer');
        }

        // 5. Add cache expiration index if cache table exists
        if (Schema::hasTable('cache')) {
            $this->createIndexIfNotExists('cache', ['expiration'], 'idx_cache_expiration');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes if they exist
        $this->dropIndexIfExists('imut_penilaians', 'idx_imut_penilaians_values');
        $this->dropIndexIfExists('imut_data', 'idx_imut_data_category_status');
        $this->dropIndexIfExists('imut_data', 'idx_imut_data_creator_status');
        $this->dropIndexIfExists('imut_data', 'idx_imut_data_slug_status');
        $this->dropIndexIfExists('imut_profil', 'idx_imut_profil_latest_version');
        $this->dropIndexIfExists('activity_log', 'idx_activity_log_subject');
        $this->dropIndexIfExists('activity_log', 'idx_activity_log_causer');
        $this->dropIndexIfExists('cache', 'idx_cache_expiration');
    }

    /**
     * Create index if it doesn't exist
     */
    private function createIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (Schema::hasTable($table) && $this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        $result = $connection->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$databaseName, $table, $indexName]
        );

        return $result[0]->count > 0;
    }
};
