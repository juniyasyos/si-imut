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
        Schema::table('region_types', function (Blueprint $table) {
            // Tambah kolom untuk custom display settings
            $table->string('display_color', 7)->nullable()->after('type')
                ->comment('Hex color untuk chart benchmarking (e.g., #10b981)');

            $table->enum('chart_type', ['line', 'column', 'area'])->default('column')->after('display_color')
                ->comment('Tipe chart untuk benchmarking di grafik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('region_types', function (Blueprint $table) {
            $table->dropColumn(['display_color', 'chart_type']);
        });
    }
};
