<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laporan_imuts', function (Blueprint $table) {
            $table->unsignedInteger('recommendation_analysis_duration')
                ->default(2)
                ->after('assessment_period_end')
                ->comment('Durasi pengisian analisis dan rekomendasi dalam hari (dari setting)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_imuts', function (Blueprint $table) {
            $table->dropColumn('recommendation_analysis_duration');
        });
    }
};
