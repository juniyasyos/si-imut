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
        Schema::table('imut_penilaians', function (Blueprint $table) {
            // Memastikan kolom analysis dan recommendations nullable
            $table->text('analysis')->nullable()->change();
            $table->text('recommendations')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_penilaians', function (Blueprint $table) {
            // Kembalikan ke required jika rollback
            $table->text('analysis')->nullable(false)->change();
            $table->text('recommendations')->nullable(false)->change();
        });
    }
};
