<?php

namespace App\Factories;

use App\Models\LaporanImut;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LaporanImutFactory extends BaseModelFactory
{
    public function create(array $attributes = []): LaporanImut
    {
        $attributes = $this->validateAttributes($attributes);
        $attributes = $this->applyDefaults($attributes);

        $laporan = LaporanImut::create($attributes);

        return $this->afterCreate($laporan);
    }

    protected function validateAttributes(array $attributes): array
    {
        // Ensure required fields
        if (empty($attributes['name'])) {
            throw new \InvalidArgumentException('Laporan name is required');
        }

        if (empty($attributes['assessment_period_start'])) {
            throw new \InvalidArgumentException('Assessment period start is required');
        }

        if (empty($attributes['assessment_period_end'])) {
            throw new \InvalidArgumentException('Assessment period end is required');
        }

        // Validate date format
        if (isset($attributes['assessment_period_start'])) {
            $attributes['assessment_period_start'] = Carbon::parse($attributes['assessment_period_start'])->format('Y-m-d');
        }

        if (isset($attributes['assessment_period_end'])) {
            $attributes['assessment_period_end'] = Carbon::parse($attributes['assessment_period_end'])->format('Y-m-d');
        }

        return $attributes;
    }

    protected function getDefaults(): array
    {
        return [
            'status' => LaporanImut::STATUS_PROCESS,
            'created_by' => Auth::id() ?? User::where('name', 'admin')->value('id'),
            'slug' => null, // Will be auto-generated in model
        ];
    }

    protected function afterCreate($laporan): LaporanImut
    {
        // Create LaporanUnitKerja relationships for all active unit kerjas
        $this->createUnitKerjaRelationships($laporan);

        return $laporan;
    }

    /**
     * Create laporan unit kerja relationships
     */
    private function createUnitKerjaRelationships(LaporanImut $laporan): void
    {
        $unitKerjas = \App\Models\UnitKerja::all();

        foreach ($unitKerjas as $unitKerja) {
            \App\Models\LaporanUnitKerja::create([
                'laporan_imut_id' => $laporan->id,
                'unit_kerja_id' => $unitKerja->id,
            ]);
        }
    }

    /**
     * Create monthly laporan for current period
     */
    public function createMonthlyLaporan(?Carbon $date = null): LaporanImut
    {
        $date = $date ?? Carbon::now();
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        return $this->create([
            'name' => "Laporan IMUT Periode {$date->format('m/Y')}",
            'assessment_period_start' => $start,
            'assessment_period_end' => $end,
        ]);
    }

    /**
     * Create yearly laporan
     */
    public function createYearlyLaporan(?int $year = null): LaporanImut
    {
        $year = $year ?? Carbon::now()->year;
        $start = Carbon::createFromDate($year, 1, 1);
        $end = Carbon::createFromDate($year, 12, 31);

        return $this->create([
            'name' => "Laporan IMUT Tahunan {$year}",
            'assessment_period_start' => $start,
            'assessment_period_end' => $end,
        ]);
    }
}
