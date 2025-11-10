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

        // Get some IMUT Data
        $imutDataList = ImutData::take(5)->get();

        if ($imutDataList->isEmpty()) {
            $this->command->warn('No IMUT Data found. Skipping ImutDataNote seeder.');
            return;
        }

        // Get some Laporan IMUT
        $laporanList = LaporanImut::take(3)->pluck('id')->toArray();

        foreach ($imutDataList as $imutData) {
            // Create 2-3 notes per IMUT Data
            $noteCount = rand(2, 3);

            for ($i = 1; $i <= $noteCount; $i++) {
                $year = rand(2023, 2025);
                $periodType = rand(0, 1) ? 'tahunan' : 'triwulan';
                $quarter = $periodType === 'triwulan' ? ['Q1', 'Q2', 'Q3', 'Q4'][rand(0, 3)] : null;

                $periodLabel = $periodType === 'tahunan'
                    ? "Tahunan {$year}"
                    : "Triwulan {$quarter} {$year}";

                ImutDataNote::create([
                    'imut_data_id' => $imutData->id,
                    'note_name' => "Catatan {$i} - {$imutData->title}",
                    'period_year' => $year,
                    'period_quarter' => $quarter,
                    'period_type' => $periodType,
                    'related_laporan_ids' => !empty($laporanList) ? array_slice($laporanList, 0, rand(1, count($laporanList))) : null,
                    'recommendation' => "Rekomendasi untuk periode {$periodLabel}:\n1. Pastikan data lengkap dan akurat\n2. Lakukan validasi berkala\n3. Update profil sesuai kebutuhan",
                    'analysis' => "Berdasarkan analisis {$periodLabel}:\n- Tren data menunjukkan peningkatan\n- Perlu perhatian khusus pada konsistensi data\n- Target pencapaian dapat ditingkatkan",
                    'additional_notes' => "Catatan tambahan:\n- Follow up dengan unit kerja terkait\n- Koordinasi dengan tim kualitas\n- Dokumentasi lengkap tersedia",
                    'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                    'is_active' => true,
                    'created_by' => $admin->id,
                ]);
            }
        }

        $this->command->info('ImutDataNote seeder completed successfully!');
    }
}
