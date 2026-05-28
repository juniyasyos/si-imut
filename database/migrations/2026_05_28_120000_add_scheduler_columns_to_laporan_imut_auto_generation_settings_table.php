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
            if (! Schema::hasColumn('laporan_imut_auto_generation_settings', 'schedule_day_of_month')) {
                $table->unsignedTinyInteger('schedule_day_of_month')
                    ->default(1)
                    ->after('frequency')
                    ->comment('Tanggal scheduler generate bulanan (1-28)');
            }

            if (! Schema::hasColumn('laporan_imut_auto_generation_settings', 'schedule_run_time')) {
                $table->string('schedule_run_time', 5)
                    ->default('01:00')
                    ->after('schedule_day_of_month')
                    ->comment('Jam scheduler generate bulanan (HH:MM)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_imut_auto_generation_settings', function (Blueprint $table) {
            if (Schema::hasColumn('laporan_imut_auto_generation_settings', 'schedule_run_time')) {
                $table->dropColumn('schedule_run_time');
            }

            if (Schema::hasColumn('laporan_imut_auto_generation_settings', 'schedule_day_of_month')) {
                $table->dropColumn('schedule_day_of_month');
            }
        });
    }
};
