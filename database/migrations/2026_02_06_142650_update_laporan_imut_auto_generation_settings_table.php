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
            // Add new column
            $table->enum('report_month_based_on', ['start', 'end'])->default('start')->after('period_end_day')->comment('Nama laporan berdasarkan bulan awal atau akhir periode');

            // Replace duration columns
            $table->renameColumn('analysis_duration', 'recommendation_analysis_duration');
            
            // Drop unnecessary columns
            $table->dropColumn(['recommendation_duration', 'grace_period', 'analysis_template', 'recommendation_template', 'required_fields', 'require_approval']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_imut_auto_generation_settings', function (Blueprint $table) {
            // Drop added column
            $table->dropColumn('report_month_based_on');

            // Rename back
            $table->renameColumn('recommendation_analysis_duration', 'analysis_duration');

            // Re-add dropped columns
            $table->integer('recommendation_duration')->default(2)->after('analysis_duration')->comment('Durasi rekomendasi (hari)');
            $table->integer('grace_period')->default(2)->after('recommendation_duration')->comment('Grace period setelah deadline (hari)');
            $table->text('analysis_template')->nullable()->comment('Template default untuk analisis');
            $table->text('recommendation_template')->nullable()->comment('Template default untuk rekomendasi');
            $table->json('required_fields')->nullable()->comment('Field wajib yang harus diisi');
            $table->boolean('require_approval')->default(false)->comment('Perlu approval sebelum finalize');
        });
    }
};
