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
            $table->json('history_suggestions')->nullable()->after('validation_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enhanced_form_fields', function (Blueprint $table) {
            $table->dropColumn('history_suggestions');
        });
    }
};
