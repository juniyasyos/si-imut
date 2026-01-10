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
        // Cek apakah kolom imut_data_id sudah ada
        if (!Schema::hasColumn('region_types', 'imut_data_id')) {
            Schema::table('region_types', function (Blueprint $table) {
                // Tambah kolom imut_data_id sebagai nullable dulu
                $table->unsignedBigInteger('imut_data_id')->nullable()->after('id');
            });
        }

        // Hapus unique constraint dari type jika masih ada
        try {
            Schema::table('region_types', function (Blueprint $table) {
                $table->dropUnique(['type']);
            });
        } catch (\Exception $e) {
            // Ignore jika constraint tidak ada
        }

        // Hapus semua data benchmarking dan region_types yang sudah ada karena strukturnya berubah
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('imut_benchmarkings')->delete();
        DB::table('region_types')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::table('region_types', function (Blueprint $table) {
            // Sekarang set NOT NULL dan tambah foreign key
            $table->unsignedBigInteger('imut_data_id')->nullable(false)->change();

            // Tambah foreign key jika belum ada
            try {
                $table->foreign('imut_data_id')->references('id')->on('imut_data')->onDelete('cascade');
            } catch (\Exception $e) {
                // Ignore jika foreign key sudah ada
            }

            // Tambah unique constraint gabungan jika belum ada
            try {
                $table->unique(['imut_data_id', 'type'], 'region_types_imut_data_type_unique');
            } catch (\Exception $e) {
                // Ignore jika constraint sudah ada
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('region_types', function (Blueprint $table) {
            // Hapus unique constraint gabungan
            $table->dropUnique('region_types_imut_data_type_unique');

            // Kembalikan unique constraint type
            $table->unique('type');

            // Hapus foreign key
            $table->dropForeign(['imut_data_id']);
            $table->dropColumn('imut_data_id');
        });
    }
};
