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
        Schema::table('imut_benchmarkings', function (Blueprint $table) {
            // Remove the region_name column
            if (Schema::hasColumn('imut_benchmarkings', 'region_name')) {
                $table->dropColumn('region_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imut_benchmarkings', function (Blueprint $table) {
            // Restore the region_name column
            $table->string('region_name')->after('region_type_id')->nullable()->comment('Nama wilayah (untuk region_type yang tidak memiliki relasi ke tabel regions)');
        });
    }
};
