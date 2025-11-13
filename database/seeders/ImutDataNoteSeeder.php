<?php

namespace Database\Seeders;

use App\Models\ImutData;
use App\Models\ImutDataNote;
use App\Models\LaporanImut;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ImutDataNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('name', 'admin')->first();

        if (!$admin) {
            $this->command->warn('Admin user not found. Skipping ImutDataNote seeder.');
            return;
        }

        // Get all IMUT Data
        $imutDataList = ImutData::all();

        if ($imutDataList->isEmpty()) {
            $this->command->warn('No IMUT Data found. Skipping ImutDataNote seeder.');
            return;
        }

        // Get some Laporan IMUT
        $laporanList = LaporanImut::pluck('id')->toArray();

        $this->command->info('Creating comprehensive IMUT Data Notes...');

        // Sample realistic analysis templates
        $analysisTemplates = [
            'achievement_high' => [
                'Capaian indikator ini menunjukkan performa sangat baik dan konsisten di seluruh unit kerja. Tim telah menunjukkan komitmen tinggi dalam menerapkan standar operasional prosedur (SOP) yang telah ditetapkan.',
                'Hasil monitoring menunjukkan peningkatan signifikan dibandingkan periode sebelumnya. Faktor utama keberhasilan adalah sosialisasi yang efektif dan pelatihan berkala kepada seluruh staf terkait.',
                'Pencapaian yang melebihi target standar ini mencerminkan budaya mutu yang sudah tertanam baik di organisasi. Dokumentasi dan pelaksanaan prosedur dilakukan dengan konsisten dan terukur.',
            ],
            'achievement_moderate' => [
                'Capaian indikator berada pada level yang cukup baik namun masih perlu peningkatan untuk mencapai target standar. Terdapat beberapa unit kerja yang konsisten mencapai target, namun beberapa unit lain masih perlu perbaikan.',
                'Analisis data menunjukkan variasi capaian antar unit kerja. Unit dengan capaian tinggi memiliki sistem dokumentasi yang lebih baik dan pemahaman SOP yang lebih mendalam.',
                'Gap antara capaian aktual dengan target standar cukup kecil, menunjukkan potensi untuk mencapai target dengan perbaikan-perbaikan minor pada sistem dan prosedur yang ada.',
            ],
            'achievement_low' => [
                'Capaian indikator masih di bawah target standar yang ditetapkan. Analisis menunjukkan adanya gap signifikan antara praktik di lapangan dengan standar yang diharapkan.',
                'Beberapa faktor penyebab rendahnya capaian antara lain: kurangnya pemahaman tentang pentingnya indikator ini, keterbatasan sumber daya, dan sistem dokumentasi yang belum optimal.',
                'Data menunjukkan inkonsistensi dalam pelaksanaan prosedur di berbagai unit kerja. Diperlukan intervensi sistematis untuk meningkatkan compliance terhadap standar yang telah ditetapkan.',
            ],
        ];

        // Sample realistic recommendations
        $recommendationTemplates = [
            'achievement_high' => [
                "1. Pertahankan capaian dengan melakukan monitoring rutin dan evaluasi berkala untuk memastikan konsistensi kinerja\n2. Dokumentasikan best practices dari unit-unit yang berhasil mencapai target untuk dijadikan pembelajaran bagi unit lain\n3. Berikan apresiasi dan reward kepada tim yang telah mencapai target sebagai bentuk pengakuan atas dedikasi mereka\n4. Tingkatkan kapasitas tim melalui pelatihan lanjutan dan workshop untuk mempertahankan standar kualitas yang tinggi",
            ],
            'achievement_moderate' => [
                "1. Identifikasi unit kerja dengan capaian rendah dan lakukan root cause analysis untuk memahami hambatan yang dihadapi\n2. Lakukan peer learning session dimana unit dengan capaian tinggi berbagi pengalaman dan strategi mereka kepada unit lain\n3. Tingkatkan frekuensi monitoring dan feedback untuk unit yang belum mencapai target, dengan pendampingan intensif\n4. Review dan perbaiki SOP yang ada berdasarkan feedback dari pelaksana di lapangan untuk memastikan implementabilitas yang lebih baik",
            ],
            'achievement_low' => [
                "1. Lakukan intensive training dan refreshment kepada seluruh staf terkait pentingnya indikator ini dan cara pelaksanaan yang benar sesuai SOP\n2. Bentuk tim khusus untuk melakukan root cause analysis mendalam dan menyusun action plan perbaikan dengan timeline yang jelas\n3. Tingkatkan sistem monitoring dengan menggunakan checklist dan audit berkala untuk memastikan compliance terhadap standar\n4. Alokasikan sumber daya yang memadai (SDM, sarana, prasarana) untuk mendukung pencapaian target indikator\n5. Buat mekanisme reward dan punishment yang jelas untuk mendorong peningkatan kinerja",
            ],
        ];

        $additionalNotesTemplates = [
            'Catatan penting: Pastikan semua data didokumentasikan dengan lengkap dan akurat pada sistem informasi.',
            'Perlu koordinasi dengan bagian terkait untuk sinkronisasi data dan harmonisasi kebijakan.',
            'Monitoring akan dilakukan secara intensif pada bulan-bulan mendatang untuk memastikan sustainability.',
            'Laporan ini akan disampaikan kepada manajemen sebagai bahan evaluasi kinerja organisasi.',
            'Rekomendasi ini disusun berdasarkan analisis data dan best practices di bidang mutu pelayanan kesehatan.',
        ];

        foreach ($imutDataList as $imutData) {
            // Create 3-5 notes for each IMUT Data
            $noteCount = rand(3, 5);

            for ($i = 0; $i < $noteCount; $i++) {
                $year = 2024 + ($i % 2); // 2024 or 2025
                $periodType = $i === 0 ? 'tahunan' : 'triwulan';
                $quarter = $periodType === 'triwulan' ? ['Q1', 'Q2', 'Q3', 'Q4'][$i % 4] : null;

                $periodLabel = $periodType === 'tahunan'
                    ? "Tahunan {$year}"
                    : "Triwulan {$quarter} {$year}";

                // Determine achievement level for template selection
                $achievementLevel = rand(70, 110); // Simulate achievement percentage

                if ($achievementLevel >= 100) {
                    $templateType = 'achievement_high';
                } elseif ($achievementLevel >= 80) {
                    $templateType = 'achievement_moderate';
                } else {
                    $templateType = 'achievement_low';
                }

                $analysis = $analysisTemplates[$templateType][array_rand($analysisTemplates[$templateType])];
                $recommendation = $recommendationTemplates[$templateType][0];
                $additionalNotes = $additionalNotesTemplates[array_rand($additionalNotesTemplates)];

                // Get related laporans for this period
                $relatedLaporans = !empty($laporanList) ? array_slice($laporanList, 0, rand(1, min(3, count($laporanList)))) : null;

                ImutDataNote::create([
                    'imut_data_id' => $imutData->id,
                    'note_name' => "Catatan Analisis {$periodLabel} - {$imutData->title}",
                    'period_year' => $year,
                    'period_quarter' => $quarter,
                    'period_type' => $periodType,
                    'related_laporan_ids' => $relatedLaporans,
                    'recommendation' => $recommendation,
                    'analysis' => $analysis,
                    'additional_notes' => $additionalNotes,
                    'priority' => ['high', 'medium', 'low'][array_rand(['high', 'medium', 'low'])],
                    'is_active' => $i === 0, // Only the latest note is active
                    'created_by' => $admin->id,
                ]);
            }

            $this->command->info("Created {$noteCount} notes for: {$imutData->title}");
        }

        $this->command->info('ImutDataNote seeder completed successfully!');
    }
}
