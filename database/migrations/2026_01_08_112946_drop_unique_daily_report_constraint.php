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
        Schema::table('daily_report_entries', function (Blueprint $table) {
            // Drop the unique constraint that prevents multiple daily reports
            $table->dropUnique('unique_daily_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_entries', function (Blueprint $table) {
            // Add back the unique constraint if needed to rollback
            $table->unique(['form_template_id', 'unit_kerja_id', 'report_date'], 'unique_daily_report');
        });
    }
};
