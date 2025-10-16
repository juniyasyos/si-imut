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
        Schema::table('imut_profil', function (Blueprint $table) {
            // Periode validitas profil
            $table->date('valid_from')->nullable()->after('version')
                ->comment('Tanggal mulai berlaku profil');
            $table->date('valid_until')->nullable()->after('valid_from')
                ->comment('Tanggal berakhir berlaku profil (null = berlaku selamanya)');

            // Index untuk performance query berdasarkan periode
            $table->index(['valid_from', 'valid_until'], 'idx_validity_period');
            $table->index(['imut_data_id', 'valid_from'], 'idx_data_validity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_profil', function (Blueprint $table) {
            $table->dropIndex('idx_validity_period');
            $table->dropIndex('idx_data_validity');
            $table->dropColumn(['valid_from', 'valid_until']);
        });
    }
};
