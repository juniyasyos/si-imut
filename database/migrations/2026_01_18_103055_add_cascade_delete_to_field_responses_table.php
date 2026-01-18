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
        Schema::table('field_responses', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['form_field_id']);
            // Add foreign key with cascade delete
            $table->foreign('form_field_id')->references('id')->on('enhanced_form_fields')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('field_responses', function (Blueprint $table) {
            // Reverse the changes - remove cascade delete
            $table->dropForeign(['form_field_id']);
            $table->foreign('form_field_id')->references('id')->on('enhanced_form_fields');
        });
    }
};
