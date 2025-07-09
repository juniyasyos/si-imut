<?php

namespace Database\Seeders;

use Database\Seeders\Traits\ImutInitializer;
use App\Models\ImutData;
use App\Models\ImutBenchmarking;
use App\Models\RegionType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ImutBenchmarkingSeeder extends Seeder
{
    use ImutInitializer;

    public function run(): void
    {
        $this->initImut();
        $regions = RegionType::all();
        $laporans = \App\Models\LaporanImut::all();

        ImutData::whereHas('category', fn($q) => $q->where('is_benchmark_category', true))
            ->get()
            ->each(function ($d) use ($regions, $laporans) {
                foreach ($laporans as $lap) {
                    $y = Carbon::parse($lap->assessment_period_start)->year;
                    $m = Carbon::parse($lap->assessment_period_start)->month;
                    foreach ($regions as $r) {
                        $nm = match ($r->type) {
                            '🌐 Nasional'   => 'Indonesia',
                            '🏛️ Provinsi'   => 'Jawa Timur',
                            '🏥 Rumah Sakit' => fake()->company . ' Hospital',
                            default         => 'Unknown',
                        };
                        ImutBenchmarking::factory()->create([
                            'imut_data_id'   => $d->id,
                            'region_type_id' => $r->id,
                            'region_name'    => $nm,
                            'year'           => $y,
                            'month'          => $m,
                        ]);
                    }
                }
            });
    }
}