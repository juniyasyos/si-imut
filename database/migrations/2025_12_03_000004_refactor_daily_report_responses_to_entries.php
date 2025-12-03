<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('daily_report_responses', 'daily_report_entries');

        Schema::table('daily_report_entries', function (Blueprint $table) {
            $table->time('entry_time')->nullable()->after('report_date');
            $table->dropColumn(['numerator_value', 'denominator_value', 'notes']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_report_entries', function (Blueprint $table) {
            $table->decimal('numerator_value', 10, 2)->nullable();
            $table->decimal('denominator_value', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->dropColumn('entry_time');
        });

        Schema::rename('daily_report_entries', 'daily_report_responses');
    }
};
