<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('imut_data', function (Blueprint $table) {
            // flag untuk menunjukkan apakah indikator diisi secara bulanan
            $table->boolean('is_monthly')->default(true)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_data', function (Blueprint $table) {
            $table->dropColumn('is_monthly');
        });
    }
};
