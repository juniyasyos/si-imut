<?php


namespace Database\Factories;

use App\Domains\Reporting\Models\LaporanImut;
use App\Domains\Organization\Models\UnitKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanUnitKerjaFactory extends Factory
{
    protected $model = \App\Domains\Reporting\Models\LaporanUnitKerja::class;

    public function definition(): array
    {
        return [
            'laporan_imut_id' => LaporanImut::factory(),
            'unit_kerja_id' => UnitKerja::factory(),
        ];
    }
}
