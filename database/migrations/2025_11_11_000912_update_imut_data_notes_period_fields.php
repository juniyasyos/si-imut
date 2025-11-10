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
        Schema::table('imut_data_notes', function (Blueprint $table) {
            // Drop old period columns
            $table->dropColumn(['period_start', 'period_end']);

            // Add new period columns
            $table->year('period_year')->nullable()->after('note_name');
            $table->enum('period_quarter', ['Q1', 'Q2', 'Q3', 'Q4'])->nullable()->after('period_year')->comment('Q1: Jan-Mar, Q2: Apr-Jun, Q3: Jul-Sep, Q4: Oct-Dec');
            $table->enum('period_type', ['tahunan', 'triwulan'])->default('tahunan')->after('period_quarter')->comment('Tipe periode: tahunan atau triwulan');

            // Add index for better query performance
            $table->index(['period_year', 'period_quarter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_data_notes', function (Blueprint $table) {
            // Drop new columns
            $table->dropIndex(['period_year', 'period_quarter']);
            $table->dropColumn(['period_year', 'period_quarter', 'period_type']);
        });
    }
};
