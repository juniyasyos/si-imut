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
        // Cek apakah kolom sudah ada
        if (!Schema::hasColumn('laporan_imuts', 'report_month')) {
            Schema::table('laporan_imuts', function (Blueprint $table) {
                // Menambahkan field bulan dan tahun untuk identifikasi periode yang lebih jelas
                $table->tinyInteger('report_month')->after('assessment_period_end')->nullable()->comment('Bulan laporan (1-12)');
                $table->year('report_year')->after('report_month')->nullable()->comment('Tahun laporan');
            });
        }

        // Update data existing dengan mengambil bulan/tahun dari assessment_period_start
        DB::statement("
            UPDATE laporan_imuts
            SET
                report_month = MONTH(assessment_period_start),
                report_year = YEAR(assessment_period_start)
            WHERE report_month IS NULL OR report_year IS NULL
        ");

        // Hapus duplikasi berdasarkan periode yang sama, simpan yang terbaru
        DB::statement("
            DELETE l1 FROM laporan_imuts l1
            INNER JOIN laporan_imuts l2
            WHERE l1.id < l2.id
            AND l1.report_year = l2.report_year
            AND l1.report_month = l2.report_month
            AND l1.deleted_at IS NULL
            AND l2.deleted_at IS NULL
        ");

        Schema::table('laporan_imuts', function (Blueprint $table) {
            // Sekarang buat field required dan tambah constraints jika belum ada
            if (Schema::hasColumn('laporan_imuts', 'report_month')) {
                $table->tinyInteger('report_month')->nullable(false)->change();
                $table->year('report_year')->nullable(false)->change();
            }

            // Menambah index untuk kombinasi bulan dan tahun jika belum ada
            if (!Schema::hasIndex('laporan_imuts', 'idx_laporan_periode')) {
                $table->index(['report_year', 'report_month'], 'idx_laporan_periode');
            }

            // Menambahkan unique constraint untuk kombinasi tahun-bulan jika belum ada
            if (!Schema::hasIndex('laporan_imuts', 'unique_periode_laporan')) {
                $table->unique(['report_year', 'report_month'], 'unique_periode_laporan');
            }
        });

        // Drop unique constraint pada name agar bisa edit nama tanpa konflik
        Schema::table('laporan_imuts', function (Blueprint $table) {
            // Cek apakah unique constraint pada name ada
            $indexes = Schema::getIndexes('laporan_imuts');
            $nameUniqueExists = collect($indexes)->contains(function($index) {
                return in_array('name', $index['columns']) && $index['unique'];
            });

            if ($nameUniqueExists) {
                $table->dropUnique(['name']);
            }

            // Ubah name menjadi nullable dan buat index biasa (bukan unique)
            $table->string('name')->nullable()->change();

            if (!Schema::hasIndex('laporan_imuts', 'idx_laporan_name')) {
                $table->index('name', 'idx_laporan_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_imuts', function (Blueprint $table) {
            // Kembalikan unique constraint pada name
            $table->dropIndex(['name']);
            $table->string('name')->unique()->change();

            // Drop index dan constraint periode
            $table->dropUnique('unique_periode_laporan');
            $table->dropIndex('idx_laporan_periode');

            // Drop kolom yang ditambahkan
            $table->dropColumn(['report_month', 'report_year']);
        });
    }
};
