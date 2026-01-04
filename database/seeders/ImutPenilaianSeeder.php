<?php

namespace Database\Seeders;

use App\Models\ImutPenilaian;
use App\Traits\ImutInitializer;
use App\Models\ImutProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImutPenilaianSeeder extends Seeder
{
    use ImutInitializer;

    public function run(): void
    {
        $this->initImut();

        $lapUnits = DB::table('laporan_unit_kerjas')->get();
        $now = now();
        $faker = fake();
        $rows = [];

        foreach ($lapUnits as $lap) {
            // Ambil laporan untuk mendapat assessment period
            $laporan = \App\Models\LaporanImut::find($lap->laporan_imut_id);

            if (!$laporan) continue;

            // Ambil semua ImutData yang aktif
            $imutDataList = \App\Models\ImutData::all();

            foreach ($imutDataList as $imutData) {
                // GUNAKAN LOGIC YANG SAMA DENGAN ProsesPenilaianImut
                $selectedProfile = $imutData->profiles()
                    ->validForPeriod(
                        $laporan->assessment_period_start,
                        $laporan->assessment_period_end
                    )
                    ->orderBy('valid_from', 'desc') // Logic yang sudah diperbaiki
                    ->first();

                // Skip jika tidak ada profil valid
                if (!$selectedProfile) continue;

                $den = rand(80, 120);
                $num = rand((int)($den * 0.7), $den);

                $rows[] = [
                    'imut_profil_id'         => $selectedProfile->id,
                    'laporan_unit_kerja_id'  => $lap->id,
                    'analysis'               => $faker->sentence(2),
                    'recommendations'        => $faker->sentence(15),
                    'numerator_value'        => $num,
                    'denominator_value'      => $den,
                    'created_at'             => $laporan->assessment_period_start->addDays(rand(0, 5)),
                    'updated_at'             => $now,
                ];
            }
        }

        // Bulk insert sekaligus
        foreach (array_chunk($rows, 1000) as $chunk) {
            ImutPenilaian::insert($chunk);
        }
    }
}
