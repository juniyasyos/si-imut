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
            $table->json('compliance_rules')->nullable()->after('conditional_logic');
        });

        Schema::table('form_field_options', function (Blueprint $table) {
            $table->boolean('is_correct')->default(true)->after('option_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enhanced_form_fields', function (Blueprint $table) {
            $table->dropColumn('compliance_rules');
        });

        Schema::table('form_field_options', function (Blueprint $table) {
            $table->dropColumn('is_correct');
        });
    }
};
