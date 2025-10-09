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
        // 1. Remove deleted_at from region_types table
        if (Schema::hasColumn('region_types', 'deleted_at')) {
            Schema::table('region_types', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // 2. Remove deleted_at from imut_benchmarkings table
        if (Schema::hasColumn('imut_benchmarkings', 'deleted_at')) {
            Schema::table('imut_benchmarkings', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restore deleted_at to region_types table
        Schema::table('region_types', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 2. Restore deleted_at to imut_benchmarkings table
        Schema::table('imut_benchmarkings', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
