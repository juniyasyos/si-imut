<?php

namespace Database\Seeders;

use App\Domains\Imut\Models\ImutPenilaian;
use App\Traits\ImutInitializer;
use App\Domains\Imut\Models\ImutProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImutPenilaianSeeder extends Seeder
{
    use ImutInitializer;

    public function run(): void
    {
        $this->initImut();

        $lapUnits = DB::table('laporan_unit_kerjas')->get()->keyBy(fn($r) => "{$r->laporan_imut_id}-{$r->unit_kerja_id}");
        $imutProfiles = ImutProfile::all();
        $now = now();
        $faker = fake();
        $rows = [];

        foreach ($imutProfiles as $p) {
            foreach ($lapUnits as $lap) {
                $den = rand(80, 120);
                $num = rand((int)($den * 0.7), $den);

                $rows[] = [
                    'imut_profil_id'         => $p->id,
                    'laporan_unit_kerja_id'  => $lap->id,
                    'analysis'               => $faker->sentence(2),
                    'recommendations'        => $faker->sentence(15),
                    'numerator_value'        => $num,
                    'denominator_value'      => $den,
                    'created_at'             => Carbon::parse($p->start_period)->addDays(rand(0, 90)),
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