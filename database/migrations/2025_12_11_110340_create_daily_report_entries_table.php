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
            $table->foreignId('daily_report_id')->constrained('daily_reports')->onDelete('cascade');
            $table->foreignId('unit_kerja_id')->constrained('unit_kerja')->onDelete('cascade');
            $table->foreignId('imut_data_id')->constrained('imut_data')->onDelete('cascade');
            $table->date('report_date');
            $table->decimal('numerator', 10, 2)->nullable();
            $table->decimal('denominator', 10, 2)->nullable();
            $table->decimal('result', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['daily_report_id', 'report_date']);
            $table->index(['unit_kerja_id', 'report_date']);
            $table->index(['imut_data_id', 'report_date']);
            $table->unique(['daily_report_id', 'imut_data_id'], 'unique_report_indicator');
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
