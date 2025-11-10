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
        Schema::create('imut_data_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imut_data_id')->constrained('imut_data')->cascadeOnDelete();
            $table->index('imut_data_id');

            // Informasi dasar note
            $table->string('note_name', 255);

            // Untuk periode apa (Triwulan & Tahunan)
            $table->year('period_year')->nullable()->comment('Tahun periode');
            $table->enum('period_quarter', ['Q1', 'Q2', 'Q3', 'Q4'])->nullable()->comment('Q1: Jan-Mar, Q2: Apr-Jun, Q3: Jul-Sep, Q4: Oct-Des');
            $table->enum('period_type', ['tahunan', 'triwulan'])->default('tahunan')->comment('Tipe periode: tahunan atau triwulan');
            $table->index(['period_year', 'period_quarter']);

            // Untuk laporan apa saja (multiple laporan)
            $table->json('related_laporan_ids')->nullable()->comment('Array of laporan_imut IDs');

            // Rekomendasi dan analisis
            $table->text('recommendation')->nullable();
            $table->text('analysis')->nullable();

            // Note tambahan lainnya
            $table->text('additional_notes')->nullable();

            // Priority/importance level
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');

            // Status
            $table->boolean('is_active')->default(true);

            // Created by
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imut_data_notes');
    }
};
