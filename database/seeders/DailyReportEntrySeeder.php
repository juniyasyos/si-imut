<?php

namespace Database\Seeders;

use App\Models\DailyReportEntry;
use App\Models\FormHeader;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DailyReportEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Preferensi: cari FormTemplate yang relevan (modern). Jika tidak ada, fallback ke legacy FormHeader.
        $formTemplate = FormTemplate::where('title', 'LIKE', '%Kebersihan Tangan%')->first();
        $formHeader = null;

        if (!$formTemplate) {
            $formHeader = FormHeader::whereHas('imutdata', function ($query) {
                $query->where('title', 'LIKE', '%Kebersihan Tangan%');
            })->first();

            if (!$formHeader) {
                $this->command->error('Form Template / Header Kebersihan Tangan tidak ditemukan!');
                return;
            }
        }

        // Cari unit kerja (contoh: IGD)
        $unitKerja = UnitKerja::where('unit_name', 'LIKE', '%IGD%')->first();

        if (!$unitKerja) {
            $this->command->error('Unit Kerja tidak ditemukan!');
            return;
        }

        // Cari user sebagai pembuat entry
        $user = User::first();

        if (!$user) {
            $this->command->error('User tidak ditemukan!');
            return;
        }

        $formLabel = $formTemplate->title ?? $formHeader->title;
        $this->command->info("Membuat data untuk Form: {$formLabel}");
        $this->command->info("Unit Kerja: {$unitKerja->unit_name}");
        $this->command->info("User: {$user->name}");

        // Data profesi dan lokasi
        $professions = ['Dokter', 'Perawat', 'Bidan'];
        $locations = ['Ruang IGD', 'Triase', 'Ruang Observasi'];
        $moments = [
            'Sebelum kontak dengan pasien',
            'Sebelum tindakan aseptik',
            'Setelah kontak dengan cairan tubuh pasien',
            'Setelah kontak dengan pasien',
            'Setelah kontak dengan lingkungan pasien'
        ];
        $methods = ['Handrub (alcohol-based)', 'Handwash (sabun dan air)'];

        // Generate data untuk 4 hari kebelakang
        $today = Carbon::today();
        $createdCount = 0;

        for ($i = 0; $i < 4; $i++) {
            $date = $today->copy()->subDays($i);

            // Generate 3-5 entry per hari
            $entriesPerDay = rand(3, 5);

            for ($j = 0; $j < $entriesPerDay; $j++) {
                $handHygieneDone = rand(0, 100) < 85; // 85% compliance rate

                $responses = [
                    'observer_name' => 'Observer ' . fake()->name(),
                    'healthcare_worker_profession' => fake()->randomElement($professions),
                    'observation_location' => fake()->randomElement($locations),
                    'five_moments' => fake()->randomElement($moments),
                    'hand_hygiene_performed' => $handHygieneDone,
                ];

                // Jika kebersihan tangan dilakukan, tambahkan detail metode
                if ($handHygieneDone) {
                    $responses['hand_hygiene_method'] = fake()->randomElement($methods);
                    $responses['six_steps_correct'] = rand(0, 100) < 90; // 90% correct
                    $responses['notes'] = rand(0, 100) < 30 ? fake()->sentence() : null;
                } else {
                    $responses['hand_hygiene_method'] = null;
                    $responses['six_steps_correct'] = null;
                    $responses['notes'] = 'Tidak melakukan kebersihan tangan sesuai indikasi';
                }

                DailyReportEntry::create([
                    'form_header_id' => $formHeader->id ?? null,
                    'form_template_id' => $formTemplate->id ?? null,
                    'unit_kerja_id' => $unitKerja->id,
                    'submitted_by' => $user->id,
                    'report_date' => $date,
                    'entry_time' => $date->copy()->setTime(rand(7, 16), rand(0, 59)),
                    'responses' => $responses,
                ]);

                $createdCount++;
            }

            $this->command->info("✓ Dibuat {$entriesPerDay} entry untuk tanggal {$date->format('d-m-Y')}");
        }

        $this->command->info("✅ Selesai! Total {$createdCount} entry berhasil dibuat.");
    }
}
