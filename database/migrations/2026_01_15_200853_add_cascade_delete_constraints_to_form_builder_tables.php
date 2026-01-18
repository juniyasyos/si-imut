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
        // Ensure form_templates has cascade delete to imut_profil (not imut_data)
        Schema::table('form_templates', function (Blueprint $table) {
            // Drop existing foreign key if exists
            $table->dropForeign(['imut_profile_id']);
            // Add foreign key with cascade delete
            $table->foreign('imut_profile_id')->references('id')->on('imut_profil')->onDelete('cascade');
        });

        // Ensure enhanced_form_fields has cascade delete to form_templates
        Schema::table('enhanced_form_fields', function (Blueprint $table) {
            // Drop existing foreign key if exists
            $table->dropForeign(['form_template_id']);
            // Add foreign key with cascade delete
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
        });

        // Ensure form_field_options has cascade delete to enhanced_form_fields
        Schema::table('form_field_options', function (Blueprint $table) {
            // Drop existing foreign key if exists
            $table->dropForeign(['enhanced_form_field_id']);
            // Add foreign key with cascade delete
            $table->foreign('enhanced_form_field_id')->references('id')->on('enhanced_form_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the changes - remove cascade delete constraints
        Schema::table('form_field_options', function (Blueprint $table) {
            $table->dropForeign(['enhanced_form_field_id']);
            $table->foreign('enhanced_form_field_id')->references('id')->on('enhanced_form_fields');
        });

        Schema::table('enhanced_form_fields', function (Blueprint $table) {
            $table->dropForeign(['form_template_id']);
            $table->foreign('form_template_id')->references('id')->on('form_templates');
        });

        Schema::table('form_templates', function (Blueprint $table) {
            $table->dropForeign(['imut_profile_id']);
            $table->foreign('imut_profile_id')->references('id')->on('imut_profil');
        });
    }
};
