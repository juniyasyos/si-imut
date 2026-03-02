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
            // Harus drop FK dulu sebelum drop index, karena MySQL tidak
            // mengizinkan menghapus index yang dipakai oleh foreign key constraint
            $table->dropForeign(['imut_profile_id']);

            // Hapus unique constraint lama (1 template per profile)
            $table->dropUnique('unique_form_template_per_profile');

            // Tambahkan index biasa pada imut_profile_id agar FK bisa ditambah kembali
            // (Unique constraint gabungan profile+version akan dibuat di migration
            // add_versioning_to_form_templates, setelah kolom 'version' ditambahkan)
            $table->index('imut_profile_id', 'idx_form_templates_imut_profile_id');

            // Tambahkan kembali foreign key dengan cascade delete
            $table->foreign('imut_profile_id')
                  ->references('id')
                  ->on('imut_profil')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            // Lepas FK dan index biasa, kembalikan ke unique constraint semula
            $table->dropForeign(['imut_profile_id']);
            $table->dropIndex('idx_form_templates_imut_profile_id');
            $table->unique('imut_profile_id', 'unique_form_template_per_profile');
            $table->foreign('imut_profile_id')
                  ->references('id')
                  ->on('imut_profil')
                  ->onDelete('cascade');
        });
    }
};
