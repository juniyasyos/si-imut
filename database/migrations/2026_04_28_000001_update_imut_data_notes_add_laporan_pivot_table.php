<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('imut_data_note_laporan_imut')) {
            Schema::create('imut_data_note_laporan_imut', function (Blueprint $table) {
                $table->id();
                $table->foreignId('imut_data_note_id')
                    ->constrained('imut_data_notes')
                    ->cascadeOnDelete();
                $table->foreignId('laporan_imut_id')
                    ->constrained('laporan_imuts')
                    ->cascadeOnDelete();
                $table->unique(
                    ['imut_data_note_id', 'laporan_imut_id'],
                    'idx_imut_data_note_laporan_imut'
                );
                $table->timestamps();
            });
        }

        Schema::table('imut_data_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('imut_data_notes', 'additional_notes')) {
                $table->text('additional_notes')->nullable()->after('analysis');
            }
        });

        if (Schema::hasColumn('imut_data_notes', 'related_laporan_ids')) {
            Schema::table('imut_data_notes', function (Blueprint $table) {
                $table->dropColumn('related_laporan_ids');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('imut_data_notes', 'additional_notes')) {
            Schema::table('imut_data_notes', function (Blueprint $table) {
                $table->dropColumn('additional_notes');
            });
        }

        if (Schema::hasTable('imut_data_note_laporan_imut')) {
            Schema::dropIfExists('imut_data_note_laporan_imut');
        }
    }
};
