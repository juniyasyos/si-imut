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
        Schema::table('enhanced_form_fields', function (Blueprint $table) {
            $table->integer('default_valid_duration')->default(480)->after('time_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enhanced_form_fields', function (Blueprint $table) {
            $table->dropColumn('default_valid_duration');
        });
    }
};
