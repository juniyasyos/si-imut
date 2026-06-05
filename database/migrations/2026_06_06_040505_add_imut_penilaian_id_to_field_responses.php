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
        Schema::table('field_responses', function (Blueprint $table) {
            // Add foreign key to imut_penilaians
            // Nullable initially to allow data migration time
            $table->foreignId('imut_penilaian_id')
                ->nullable()
                ->constrained('imut_penilaians')
                ->cascadeOnDelete();
            
            // Index untuk faster lookups
            $table->index('imut_penilaian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('field_responses', function (Blueprint $table) {
            $table->dropForeign(['imut_penilaian_id']);
            $table->dropIndex(['imut_penilaian_id']);
            $table->dropColumn('imut_penilaian_id');
        });
    }
};
