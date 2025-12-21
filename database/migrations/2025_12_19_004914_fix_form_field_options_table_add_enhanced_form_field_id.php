<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_field_options', function (Blueprint $table) {
            // First, add the new column
            $table->unsignedBigInteger('enhanced_form_field_id')->nullable()->after('id');
        });

        // Copy data from form_field_id to enhanced_form_field_id if there's any data
        DB::statement('UPDATE form_field_options SET enhanced_form_field_id = form_field_id WHERE form_field_id IS NOT NULL');

        Schema::table('form_field_options', function (Blueprint $table) {
            // Make the new column non-nullable
            $table->unsignedBigInteger('enhanced_form_field_id')->nullable(false)->change();

            // Add foreign key constraint
            $table->foreign('enhanced_form_field_id')->references('id')->on('enhanced_form_fields')->onDelete('cascade');

            // Add index for better performance
            $table->index(['enhanced_form_field_id', 'order_index']);

            // Remove the old column and its constraints
            $table->dropForeign(['form_field_id']);
            $table->dropColumn('form_field_id');

            // Update column names to match the model
            $table->renameColumn('option_key', 'option_value');
            $table->renameColumn('option_label', 'option_text');
            $table->dropColumn('option_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_field_options', function (Blueprint $table) {
            // Reverse the changes
            $table->unsignedBigInteger('form_field_id')->nullable()->after('id');
            $table->text('option_description')->nullable();
            $table->renameColumn('option_value', 'option_key');
            $table->renameColumn('option_text', 'option_label');
        });

        // Copy data back
        DB::statement('UPDATE form_field_options SET form_field_id = enhanced_form_field_id WHERE enhanced_form_field_id IS NOT NULL');

        Schema::table('form_field_options', function (Blueprint $table) {
            $table->unsignedBigInteger('form_field_id')->nullable(false)->change();
            $table->foreign('form_field_id')->references('id')->on('enhanced_form_fields')->onDelete('cascade');

            $table->dropForeign(['enhanced_form_field_id']);
            $table->dropIndex(['enhanced_form_field_id', 'order_index']);
            $table->dropColumn('enhanced_form_field_id');
        });
    }
};
