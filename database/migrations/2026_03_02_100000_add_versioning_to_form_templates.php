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
        Schema::table('form_templates', function (Blueprint $table) {
            // Add versioning columns
            $table->string('version', 50)->default('v1.0')->after('imut_profile_id');
            $table->boolean('is_active')->default(false)->after('version');
            $table->date('valid_from')->nullable()->after('is_active');
            $table->date('valid_until')->nullable()->after('valid_from');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->after('valid_until');
            $table->foreignId('parent_template_id')->nullable()->constrained('form_templates')->after('created_by_user_id');

            // Tambah index performa dan unique constraint versi per profile
            $table->index(['imut_profile_id', 'is_active'], 'idx_profile_active');
            // Unique constraint: satu versi unik per profile (menggantikan unique lama
            // yang dihapus di migration fix_form_templates_versioning_constraints)
            $table->unique(['imut_profile_id', 'version'], 'unique_profile_version');
            $table->index(['parent_template_id'], 'idx_parent_template');
        });

        // Migrate existing data
        DB::statement('
            UPDATE form_templates 
            SET version = "v1.0", 
                is_active = true,
                created_by_user_id = (SELECT id FROM users ORDER BY id LIMIT 1),
                valid_from = CURDATE()
        ');

        // Add unique constraint to ensure only one active template per profile
        // Note: This uses a partial unique index which may not be supported in all DB engines
        // For MySQL 8.0+ and PostgreSQL, this should work
        if (DB::getDriverName() === 'mysql') {
            // For MySQL, we'll handle this in application logic since MySQL doesn't support partial indexes well
            // Alternative: Use a trigger or handle it purely in application code
        } else {
            // For PostgreSQL
            DB::statement('
                CREATE UNIQUE INDEX CONCURRENTLY uk_one_active_per_profile 
                ON form_templates (imut_profile_id) 
                WHERE is_active = true
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            // Hapus index dan unique constraint yang ditambahkan di up()
            $table->dropIndex('idx_profile_active');
            $table->dropUnique('unique_profile_version');
            $table->dropIndex('idx_parent_template');

            // Drop foreign key constraints
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['parent_template_id']);

            // Drop columns
            $table->dropColumn([
                'version',
                'is_active',
                'valid_from',
                'valid_until',
                'created_by_user_id',
                'parent_template_id'
            ]);
        });

        // Drop partial unique index if it exists
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS uk_one_active_per_profile');
        }
    }
};
