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
        Schema::create('laporan_imut_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_imut_id')
                ->constrained('laporan_imuts')
                ->onDelete('cascade')
                ->comment('ID laporan IMUT');
            $table->foreignId('imut_data_id')
                ->constrained('imut_data')
                ->onDelete('cascade')
                ->comment('ID data IMUT');
            $table->foreignId('imut_profil_id')
                ->constrained('imut_profil')
                ->onDelete('cascade')
                ->comment('ID profil yang digunakan untuk laporan ini');
            $table->timestamp('selected_at')->useCurrent()
                ->comment('Waktu profil dipilih untuk laporan');
            $table->json('selection_metadata')->nullable()
                ->comment('Metadata pemilihan profil (alasan, auto/manual, dll)');
            $table->timestamps();

            // Unique constraint - satu laporan hanya boleh pakai satu profil per imut_data
            $table->unique(['laporan_imut_id', 'imut_data_id'], 'unique_laporan_imut_data');

            // Index untuk performance
            $table->index(['laporan_imut_id', 'imut_data_id'], 'idx_laporan_data');
            $table->index('imut_profil_id', 'idx_profil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_imut_profiles');
    }
};
