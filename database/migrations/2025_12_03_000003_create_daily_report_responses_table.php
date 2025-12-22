<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_kerja_id')->constrained('unit_kerja')->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->date('report_date');
            $table->json('responses');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['unit_kerja_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_responses');
    }
};
