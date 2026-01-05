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
        // First, copy data from start_period and end_period to valid_from and valid_until
        // for records where valid_from/valid_until are null
        DB::statement('
            UPDATE imut_profil 
            SET valid_from = start_period 
            WHERE valid_from IS NULL AND start_period IS NOT NULL
        ');

        DB::statement('
            UPDATE imut_profil 
            SET valid_until = end_period 
            WHERE valid_until IS NULL AND end_period IS NOT NULL
        ');

        Schema::table('imut_profil', function (Blueprint $table) {
            // Remove the redundant columns
            $table->dropColumn(['start_period', 'end_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_profil', function (Blueprint $table) {
            // Add back the old columns for rollback
            $table->date('start_period')->nullable()->after('analysis_period_value');
            $table->date('end_period')->nullable()->after('start_period');
        });

        // Copy data back from valid columns to period columns
        DB::statement('
            UPDATE imut_profil 
            SET start_period = valid_from 
            WHERE valid_from IS NOT NULL
        ');

        DB::statement('
            UPDATE imut_profil 
            SET end_period = valid_until 
            WHERE valid_until IS NOT NULL
        ');
    }
};
