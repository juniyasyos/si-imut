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
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->time('entry_time')->nullable();
            $table->json('responses')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_entries', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropColumn(['submitted_by', 'entry_time', 'responses']);
        });
    }
};
