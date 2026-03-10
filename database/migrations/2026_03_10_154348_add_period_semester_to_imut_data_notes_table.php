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
        Schema::table('imut_data_notes', function (Blueprint $table) {
            $table->enum('period_semester', ['S1', 'S2'])->nullable()->after('period_quarter')->comment('S1: Jan-Jun, S2: Jul-Des');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_data_notes', function (Blueprint $table) {
            $table->dropColumn('period_semester');
        });
    }
};
