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
        $lapUnits = DB::table('laporan_unit_kerjas')->get()->keyBy(fn($r) => "{$r->laporan_imut_id}-{$r->unit_kerja_id}");

        ImutProfile::all()->each(function ($p) use ($lapUnits) {
            foreach ($lapUnits as $key => $lap) {
                [$lId, $uId] = explode('-', $key);
                $den = rand(80, 120);
                $num = rand((int)($den * 0.7), $den);

                ImutPenilaian::create([
                    'imut_profil_id'         => $p->id,
                    'laporan_unit_kerja_id'  => $lap->id,
                    'analysis'               => fake()->sentence(2),
                    'recommendations'        => fake()->sentence(15),
                    'numerator_value'        => $num,
                    'denominator_value'      => $den,
                    'created_at'             => Carbon::parse($p->start_period)->addDays(rand(0, 90)),
                ]);
            }
        });
    }
}