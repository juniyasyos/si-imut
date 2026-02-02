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
        Schema::create('laporan_imut_auto_generation_settings', function (Blueprint $table) {
            $table->id();

            // Basic Settings
            $table->boolean('is_enabled')->default(false)->comment('Auto generation aktif/tidak');
            $table->enum('frequency', ['monthly', 'quarterly', 'yearly'])->default('monthly')->comment('Frekuensi pembuatan laporan');

            // Report Period Configuration
            $table->integer('period_start_day')->default(5)->comment('Tanggal mulai periode (misal: 5 untuk tanggal 5)');
            $table->integer('period_end_day')->default(4)->comment('Tanggal akhir periode (misal: 4 untuk tanggal 4 bulan berikutnya)');

            // Timeline & Deadlines (in days)
            $table->integer('data_entry_duration')->default(7)->comment('Durasi pengisian data (hari)');
            $table->integer('analysis_duration')->default(3)->comment('Durasi analisis (hari)');
            $table->integer('recommendation_duration')->default(2)->comment('Durasi rekomendasi (hari)');
            $table->integer('grace_period')->default(2)->comment('Grace period setelah deadline (hari)');

            // Automation Settings
            $table->boolean('auto_calculate')->default(true)->comment('Otomatis hitung dari daily reports');
            $table->boolean('auto_publish')->default(false)->comment('Langsung publish atau draft');
            $table->json('default_unit_kerjas')->nullable()->comment('Unit kerja default yang di-include (array of IDs)');

            // Notification Settings
            $table->json('reminder_schedule')->nullable()->comment('Jadwal reminder (array: [3, 1] = 3 hari & 1 hari sebelum deadline)');
            $table->json('notification_targets')->nullable()->comment('Target notifikasi (pic, supervisor, all)');
            $table->boolean('enable_escalation')->default(false)->comment('Notifikasi ke level atas jika terlewat');

            // Template & Quality Control
            $table->text('analysis_template')->nullable()->comment('Template default untuk analisis');
            $table->text('recommendation_template')->nullable()->comment('Template default untuk rekomendasi');
            $table->json('required_fields')->nullable()->comment('Field wajib yang harus diisi');
            $table->boolean('require_approval')->default(false)->comment('Perlu approval sebelum finalize');

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_enabled');
            $table->index('frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_imut_auto_generation_settings');
    }
};
