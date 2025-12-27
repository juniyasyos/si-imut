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
        Schema::create('daily_report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')->constrained('daily_reports')->onDelete('cascade');
            $table->foreignId('unit_kerja_id')->constrained('unit_kerja')->onDelete('cascade');
            $table->date('report_date');
            $table->timestamps();

            $table->index(['form_template_id', 'report_date']);
            $table->index(['unit_kerja_id', 'report_date']);
            $table->unique(['form_template_id'], 'unique_report_indicator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_entries');
    }
};
