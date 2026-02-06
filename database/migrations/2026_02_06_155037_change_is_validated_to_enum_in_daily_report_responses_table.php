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
        Schema::table('daily_report_responses', function (Blueprint $table) {
            // Drop the existing boolean column
            $table->dropColumn('is_validated');

            // Add new enum column
            $table->enum('validation_status', ['pending', 'valid', 'invalid'])->default('pending')->after('calculation_details');
        });
    }

    public function down(): void
    {
        Schema::table('daily_report_responses', function (Blueprint $table) {
            // Drop the enum column
            $table->dropColumn('validation_status');

            // Restore the boolean column
            $table->boolean('is_validated')->default(false)->after('calculation_details');
        });
    }
};
