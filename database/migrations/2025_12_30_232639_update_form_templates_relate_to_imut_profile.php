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
        Schema::table('form_templates', function (Blueprint $table) {
            // Drop foreign key constraint lama
            $table->dropForeign(['imut_data_id']);
            // Rename column
            $table->renameColumn('imut_data_id', 'imut_profile_id');
            // Add foreign key baru ke imut_profil
            $table->foreign('imut_profile_id')->references('id')->on('imut_profil')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            // Drop foreign key baru
            $table->dropForeign(['imut_profile_id']);
            // Rename column kembali
            $table->renameColumn('imut_profile_id', 'imut_data_id');
            // Add foreign key lama
            $table->foreign('imut_data_id')->references('id')->on('imut_data')->onDelete('cascade');
        });
    }
};
