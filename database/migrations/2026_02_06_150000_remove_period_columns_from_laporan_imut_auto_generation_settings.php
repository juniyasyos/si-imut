<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laporan_imut_auto_generation_settings', function (Blueprint $table) {
            // Drop period columns as we're using full month (1 - end of month) approach
            $table->dropColumn(['period_start_day', 'period_end_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_imut_auto_generation_settings', function (Blueprint $table) {
            // Restore period columns
            $table->integer('period_start_day')->default(5)->after('frequency')->comment('Tanggal mulai periode (misal: 5 untuk tanggal 5)');
            $table->integer('period_end_day')->default(4)->after('period_start_day')->comment('Tanggal akhir periode (misal: 4 untuk tanggal 4 bulan berikutnya)');
        });
    }
};
