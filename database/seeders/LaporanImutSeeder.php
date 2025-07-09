<?php

namespace Database\Seeders;

use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Traits\ImutInitializer;
use Illuminate\Database\Seeder;

class LaporanImutSeeder extends Seeder
{
    use ImutInitializer;

    public function run(): void
    {
        $this->initImut();

        $laporanList = [];

        $totalMonths = $this->totalYears * 12;
        for ($i = 0; $i < $totalMonths; $i++) {
            $month = $this->now->copy()->subMonths($i)->month;
            $year  = $this->now->copy()->subMonths($i)->year;
            $start = $this->now->copy()->setDate($year, $month, 1);
            $end   = $start->copy()->endOfMonth();
            $assessmentStart = $end->copy()->subDays(4);

            $laporan = LaporanImut::firstOrCreate(
                ['name' => "Laporan IMUT Periode $month/$year"],
                [
                    'assessment_period_start' => $assessmentStart,
                    'assessment_period_end'   => $end,
                    'status'                  => LaporanImut::STATUS_PROCESS,
                    'created_by'              => $this->adminUserId,
                ]
            );

            foreach ($this->unitKerjaIds as $unitId) {
                LaporanUnitKerja::firstOrCreate([
                    'laporan_imut_id' => $laporan->id,
                    'unit_kerja_id'   => $unitId,
                ]);
            }

            $laporanList[] = $laporan;
        }
    }
}