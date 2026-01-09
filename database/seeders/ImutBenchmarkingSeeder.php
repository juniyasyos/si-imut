<?php

namespace Database\Seeders;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use App\Models\User;
use App\Traits\ImutInitializer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ImutBenchmarkingSeeder extends Seeder
{
    use ImutInitializer;

    public function run(): void
    {
        $this->initImut();

        $this->command->info('🎯 Starting ImutBenchmarking Seeder...');

        $regions = RegionType::all();
        $laporans = \App\Models\LaporanImut::all();
        $users = User::all();

        if ($regions->isEmpty()) {
            $this->command->warn('⚠️  No regions found. Skipping benchmarking seed.');
            return;
        }

        if ($laporans->isEmpty()) {
            $this->command->warn('⚠️  No laporans found. Skipping benchmarking seed.');
            return;
        }

        $benchmarkIndicators = ImutData::whereHas('categories', fn($q) => $q->where('is_benchmark_category', true))->get();

        if ($benchmarkIndicators->isEmpty()) {
            $this->command->warn('⚠️  No benchmark indicators found. Skipping benchmarking seed.');
            return;
        }

        $this->command->info("📊 Found {$benchmarkIndicators->count()} benchmark indicators");
        $this->command->info("📍 Found {$regions->count()} region types");
        $this->command->info("📅 Found {$laporans->count()} laporan periods");

        $totalCreated = 0;
        $bar = $this->command->getOutput()->createProgressBar($benchmarkIndicators->count() * $laporans->count() * $regions->count());

        foreach ($benchmarkIndicators as $indicator) {
            foreach ($laporans as $laporan) {
                $y = Carbon::parse($laporan->assessment_period_start)->year;
                $m = Carbon::parse($laporan->assessment_period_start)->month;
                $periodStart = Carbon::create($y, $m, 1)->startOfMonth();
                $periodEnd = Carbon::create($y, $m, 1)->endOfMonth();

                foreach ($regions as $region) {
                    // Check if already exists to avoid duplicates
                    $exists = ImutBenchmarking::where('imut_data_id', $indicator->id)
                        ->where('region_type_id', $region->id)
                        ->whereYear('period_start', $y)
                        ->whereMonth('period_start', $m)
                        ->exists();

                    if (!$exists) {
                        ImutBenchmarking::create([
                            'imut_data_id' => $indicator->id,
                            'region_type_id' => $region->id,
                            'benchmark_value' => fake()->randomFloat(2, 70, 95),
                            'period_start' => $periodStart,
                            'period_end' => $periodEnd,
                            'is_active' => true,
                            'notes' => "Benchmarking untuk {$indicator->title} - {$region->type}",
                            'created_by' => $users->random()->id ?? null,
                            'updated_by' => $users->random()->id ?? null,
                        ]);
                        $totalCreated++;
                    }

                    $bar->advance();
                }
            }
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✅ Created {$totalCreated} benchmarking records");
    }

    /**
     * Generate region name based on type
     */
    protected function generateRegionName(string $type): string
    {
        // Remove emoji and clean type string
        $cleanType = strtolower(str_replace(['🌐', '🏛️', '🏥', ' '], '', $type));

        return match ($cleanType) {
            'nasional' => 'Indonesia',
            'provinsi' => fake()->randomElement([
                'Jawa Timur',
                'Jawa Barat',
                'Jawa Tengah',
                'DKI Jakarta',
                'Bali',
            ]),
            'rumahsakit' => fake()->company() . ' Hospital',
            default => ucfirst($type),
        };
    }
}
