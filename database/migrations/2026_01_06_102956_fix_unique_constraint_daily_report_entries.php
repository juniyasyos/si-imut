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
            // Drop old unique constraint on just form_template_id
            $table->dropUnique('unique_report_indicator');

            // Add new unique constraint on form_template_id, unit_kerja_id, and report_date
            $table->unique(['form_template_id', 'unit_kerja_id', 'report_date'], 'unique_daily_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_entries', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('unique_daily_report');

            // Restore old unique constraint on just form_template_id
            $table->unique(['form_template_id'], 'unique_report_indicator');
        });
    }
};
