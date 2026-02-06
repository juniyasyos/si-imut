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
            $table->dropColumn(['recommendation_analysis_duration_backup']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_imut_auto_generation_settings', function (Blueprint $table) {
            // Columns will be restored if needed from previous migrations
        });
    }
};
