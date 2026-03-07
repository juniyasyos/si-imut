<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_imut_auto_generation_settings', function (Blueprint $table) {
            $table->renameColumn('data_entry_duration', 'back_data_entry_duration');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_imut_auto_generation_settings', function (Blueprint $table) {
            $table->renameColumn('back_data_entry_duration', 'data_entry_duration');
        });
    }
};
