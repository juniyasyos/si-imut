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
            $table->string('time_format')->default('HM')->after('order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enhanced_form_fields', function (Blueprint $table) {
            $table->dropColumn('time_format');
        });
    }
};
