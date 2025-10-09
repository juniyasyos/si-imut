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
     * This migration fixes unique constraint conflicts with SoftDeletes by:
     * 1. Dropping regular unique indexes that conflict with soft deletes (if they exist)
     * 2. For MySQL, we'll create composite unique indexes that include deleted_at
     * 3. This prevents duplicate constraint violations when creating new records
     *    with values that match soft-deleted records
     *
     * Note: MySQL doesn't support partial indexes like PostgreSQL, so we use
     * composite unique indexes including the deleted_at column.
     */
    public function up(): void
    {
        // Check and fix ImutKategori unique constraints
        $indexes = DB::select("SHOW INDEXES FROM imut_kategori WHERE Key_name LIKE '%category_name%'");
        if (count($indexes) > 0) {
            Schema::table('imut_kategori', function (Blueprint $table) {
                $table->dropUnique(['category_name']);
            });
        }

        // Create composite unique index that includes deleted_at
        Schema::table('imut_kategori', function (Blueprint $table) {
            $table->unique(['category_name', 'deleted_at'], 'imut_kategori_name_deleted_unique');
        });

        // Check and fix ImutData unique constraints
        $indexes = DB::select("SHOW INDEXES FROM imut_data WHERE Key_name LIKE '%title%'");
        if (count($indexes) > 0) {
            Schema::table('imut_data', function (Blueprint $table) {
                $table->dropUnique(['title']);
            });
        }

        // Create composite unique index that includes deleted_at
        Schema::table('imut_data', function (Blueprint $table) {
            $table->unique(['title', 'deleted_at'], 'imut_data_title_deleted_unique');
        });

        // Check and fix ImutProfil unique constraints
        $indexes = DB::select("SHOW INDEXES FROM imut_profil WHERE Key_name LIKE '%slug%'");
        if (count($indexes) > 0) {
            Schema::table('imut_profil', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        }

        // Create composite unique index that includes deleted_at
        Schema::table('imut_profil', function (Blueprint $table) {
            $table->unique(['slug', 'deleted_at'], 'imut_profil_slug_deleted_unique');
        });

        // Check and fix UnitKerja unique constraints
        $indexes = DB::select("SHOW INDEXES FROM unit_kerja WHERE Key_name LIKE '%unit_name%'");
        if (count($indexes) > 0) {
            Schema::table('unit_kerja', function (Blueprint $table) {
                $table->dropUnique(['unit_name']);
            });
        }

        // Create composite unique index that includes deleted_at
        Schema::table('unit_kerja', function (Blueprint $table) {
            $table->unique(['unit_name', 'deleted_at'], 'unit_kerja_name_deleted_unique');
        });

        // Check and fix User unique constraints
        $nikIndexes = DB::select("SHOW INDEXES FROM users WHERE Key_name LIKE '%nik%'");
        if (count($nikIndexes) > 0) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['nik']);
            });
        }

        $emailIndexes = DB::select("SHOW INDEXES FROM users WHERE Key_name LIKE '%email%'");
        if (count($emailIndexes) > 0) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
            });
        }

        // Create composite unique indexes that include deleted_at
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['nik', 'deleted_at'], 'users_nik_deleted_unique');
            $table->unique(['email', 'deleted_at'], 'users_email_deleted_unique');
        });

        // Check and fix LaporanImuts unique constraints
        $indexes = DB::select("SHOW INDEXES FROM laporan_imuts WHERE Key_name LIKE '%slug%'");
        if (count($indexes) > 0) {
            Schema::table('laporan_imuts', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        }

        // Create composite unique index that includes deleted_at
        Schema::table('laporan_imuts', function (Blueprint $table) {
            $table->unique(['slug', 'deleted_at'], 'laporan_imuts_slug_deleted_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: This rollback recreates standard unique constraints
     * which may fail if soft-deleted records with duplicate values exist.
     * Clean up duplicate soft-deleted records before rolling back.
     */
    public function down(): void
    {
        // Drop composite unique indexes and restore regular unique constraints

        // ImutKategori
        Schema::table('imut_kategori', function (Blueprint $table) {
            $table->dropUnique('imut_kategori_name_deleted_unique');
            $table->unique('category_name');
        });

        // ImutData
        Schema::table('imut_data', function (Blueprint $table) {
            $table->dropUnique('imut_data_title_deleted_unique');
            $table->unique('title');
        });

        // ImutProfil
        Schema::table('imut_profil', function (Blueprint $table) {
            $table->dropUnique('imut_profil_slug_deleted_unique');
            $table->unique('slug');
        });

        // UnitKerja
        Schema::table('unit_kerja', function (Blueprint $table) {
            $table->dropUnique('unit_kerja_name_deleted_unique');
            $table->unique('unit_name');
        });

        // Users
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_nik_deleted_unique');
            $table->dropUnique('users_email_deleted_unique');
            $table->unique('nik');
            $table->unique('email');
        });

        // LaporanImuts
        Schema::table('laporan_imuts', function (Blueprint $table) {
            $table->dropUnique('laporan_imuts_slug_deleted_unique');
            $table->unique('slug');
        });
    }
};
