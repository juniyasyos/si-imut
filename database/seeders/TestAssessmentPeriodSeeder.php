<?php

namespace Database\Seeders;

use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestAssessmentPeriodSeeder extends Seeder
{
    /**
     * Test seeder untuk membuktikan profile selection menggunakan assessment period
     */
    public function run(): void
    {
        $this->command->info("🧪 CREATING TEST LAPORAN - 3 BULAN LALU (OKTOBER 2025)");
        $this->command->line("📅 Tanggal sekarang: " . now()->format('Y-m-d'));
        $this->command->line("🎯 Target: Laporan assessment period Oktober 2025");
        $this->command->line("");

        // 3 bulan lalu = Oktober 2025
        $targetDate = now()->subMonths(3); // Oktober 2025
        $assessmentStart = $targetDate->copy()->day(25); // 25 Oktober 2025
        $assessmentEnd = $targetDate->copy()->endOfMonth(); // 31 Oktober 2025

        $adminUserId = User::where('name', 'admin')->value('id') ?? 1;

        // Buat laporan dengan assessment period Oktober 2025
        $laporan = LaporanImut::create([
            'name' => "TEST: Laporan IMUT Oktober 2025 (3 Bulan Lalu)",
            'assessment_period_start' => $assessmentStart,
            'assessment_period_end' => $assessmentEnd,
            'status' => LaporanImut::STATUS_PROCESS,
            'created_by' => $adminUserId,
        ]);

        $this->command->comment("✅ Laporan dibuat:");
        $this->command->line("   • ID: {$laporan->id}");
        $this->command->line("   • Name: {$laporan->name}");
        $this->command->line("   • Assessment: {$assessmentStart->format('Y-m-d')} → {$assessmentEnd->format('Y-m-d')}");
        $this->command->line("");

        // Buat LaporanUnitKerja untuk beberapa unit
        $unitKerjas = UnitKerja::take(5)->get(); // Ambil 5 unit saja untuk testing

        foreach ($unitKerjas as $unitKerja) {
            LaporanUnitKerja::create([
                'laporan_imut_id' => $laporan->id,
                'unit_kerja_id' => $unitKerja->id,
            ]);
        }

        $this->command->comment("✅ LaporanUnitKerja dibuat untuk {$unitKerjas->count()} unit kerja");
        $this->command->line("");

        // Prediksi profile yang seharusnya dipilih
        $kebersihanData = \App\Models\ImutData::where('title', 'like', '%kepatuhan kebersihan tangan%')->first();

        if ($kebersihanData) {
            $predictedProfile = $kebersihanData->profiles()
                ->validForPeriod($assessmentStart, $assessmentEnd)
                ->orderBy('valid_from', 'desc')
                ->first();

            if ($predictedProfile) {
                $this->command->comment("🔮 PREDIKSI PROFILE SELECTION:");
                $this->command->line("   • Expected Profile: {$predictedProfile->version} (ID: {$predictedProfile->id})");
                $this->command->line("   • Profile Period: {$predictedProfile->valid_from->format('Y-m-d')} → {$predictedProfile->valid_until->format('Y-m-d')}");
                $this->command->line("   • Target Value: {$predictedProfile->target_value}");
                $this->command->line("");

                // Validasi periode
                $isValidPeriod = $predictedProfile->valid_from <= $assessmentEnd &&
                    ($predictedProfile->valid_until === null || $predictedProfile->valid_until >= $assessmentStart);

                $this->command->line("   ✅ Period Validation: " . ($isValidPeriod ? "VALID" : "INVALID"));
            } else {
                $this->command->warn("   ⚠️ Tidak ada profile valid untuk periode Oktober 2025");
            }
        }

        $this->command->line("");
        $this->command->info("🚀 READY FOR TESTING!");
        $this->command->line("Next steps:");
        $this->command->line("1. Dispatch ProsesPenilaianImut job untuk laporan ID: {$laporan->id}");
        $this->command->line("2. Check apakah job memilih profile yang tepat untuk Oktober 2025");
        $this->command->line("3. Verify hasil vs prediksi di atas");
    }
}
