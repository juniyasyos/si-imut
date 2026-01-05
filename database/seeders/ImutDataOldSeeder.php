<?php

namespace Database\Seeders;

use App\Models\ImutBenchmarking;
use App\Models\ImutCategory;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\RegionType;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImutDataOldSeeder extends Seeder
{
    protected $faker;

    protected $now;

    protected $adminUserId;

    protected $unitKerjaIds;

    protected $category;

    protected $laporanList = [];

    protected int $totalYears = 2; // Extend to 2 years to include 2025-2026

    public function run(): void
    {
        $this->init();

        // Temporarily disable ImutProfile validation during seeding
        putenv('DISABLE_IMUT_VALIDATION=true');

        $this->command->info("🔄 MULAI SEEDING IMUT DATA & PROFILES...");
        $this->command->line("📅 Target: Generate quarterly profiles untuk 2025-2026 (8 quarters)");
        $this->command->line("🎯 Fokus: Monitoring profile 'Kepatuhan Kebersihan Tangan'");
        $this->command->line("");

        $filesByCategoryShortName = [
            'INM' => 'inm.json',
            'IMP-UNIT' => 'imp-unit.json',
            'IMP-RS' => 'imp-rs.json',
            'IMIKP' => 'imp_kp.json',
            'UNIT' => 'unit.json'
        ];

        $this->createLaporanImut();

        foreach ($filesByCategoryShortName as $shortName => $filename) {
            $category = ImutCategory::where('short_name', $shortName)->first();

            if (! $category) {
                $this->command->warn("Kategori dengan short_name \"$shortName\" tidak ditemukan. Lewati file \"$filename\".");

                continue;
            }

            $indicators = $this->getJsonData($filename);
            if (! $indicators) {
                continue;
            }

            $this->command->comment("📂 Processing kategori: {$shortName} ({$filename})");

            collect($indicators)->chunk(50)->each(function ($chunkedIndicators) use ($category) {
                foreach ($chunkedIndicators as $indicator) {
                    $this->processIndicator($indicator, $category);
                }
            });
        }

        // Show summary at the end
        $this->showSeedingSummary();
    }

    private function init(): void
    {
        $this->faker = Faker::create();
        $this->now = Carbon::now();

        $this->category = ImutCategory::where('category_name', 'Indikator Mutu Nasional (INM)')->first();
        if (! $this->category) {
            $this->command->warn('Kategori INM tidak ditemukan. Jalankan ImutCategorySeeder terlebih dahulu.');
        }

        $this->adminUserId = User::where('name', 'admin')->value('id');
        if (! $this->adminUserId) {
            $this->command->warn('User admin tidak ditemukan.');
        }

        $this->unitKerjaIds = UnitKerja::pluck('id')->toArray();
    }

    private function getJsonData(string $filename): ?array
    {
        $filePath = database_path("data/$filename");

        if (! File::exists($filePath)) {
            $this->command->warn("File \"$filename\" tidak ditemukan di folder database/data.");

            return null;
        }

        return json_decode(File::get($filePath), true);
    }

    private function createLaporanImut(): void
    {
        $totalMonths = $this->totalYears * 12;

        for ($i = 0; $i < $totalMonths; $i++) {
            $month = $this->now->copy()->subMonths($i)->month;
            $year = $this->now->copy()->subMonths($i)->year;

            $start = Carbon::create($year, $month, 1);
            $end = $start->copy()->endOfMonth();
            $assessmentStart = $end->copy()->subDays(4);

            $laporan = LaporanImut::firstOrCreate([
                'name' => "Laporan IMUT Periode $month/$year",
            ], [
                'assessment_period_start' => $assessmentStart,
                'assessment_period_end' => $end,
                'status' => LaporanImut::STATUS_PROCESS,
                'created_by' => $this->adminUserId ?? 1,
            ]);

            foreach ($this->unitKerjaIds as $unitKerjaId) {
                LaporanUnitKerja::firstOrCreate([
                    'laporan_imut_id' => $laporan->id,
                    'unit_kerja_id' => $unitKerjaId,
                ]);
            }

            $this->laporanList[] = $laporan;
        }
    }



    private function processIndicator(array $indicator, ImutCategory $category): void
    {
        try {
            $imutData = ImutData::firstOrCreate([
                'title' => $indicator['title'],
                'imut_kategori_id' => $category->id,
                'description' => $indicator['description'],
                'status' => true,
                'created_by' => $this->adminUserId ?? 1,
            ]);

            $profile = $indicator['profile'];

            $requiredKeys = [
                'rationale',
                'quality_dimension',
                'objective',
                'operational_definition',
                'indicator_type',
                'numerator_formula',
                'denominator_formula',
                'target_value',
                'inclusion_criteria',
                'exclusion_criteria',
                'data_source',
                'data_collection_frequency',
                'analysis_plan',
                'analysis_period_type',
                'analysis_period_value',
                'data_collection_method',
                'sampling_method',
                'data_collection_tool',
                'responsible_person',
            ];

            $missing = array_diff($requiredKeys, array_keys($profile));
            if (!empty($missing)) {
                throw new \Exception("Missing keys in profile: " . implode(', ', $missing));
            }

            $indicatorType = in_array($profile['indicator_type'], ['process', 'outcome', 'output'])
                ? $profile['indicator_type']
                : 'process';

            $analysisPeriodType = $profile['analysis_period_type'];
            $analysisPeriodValue = (int) $profile['analysis_period_value'];

            $startPeriod = now()->startOfYear(); // 2026-01-01

            // Extend end period to future based on analysis period
            $endPeriod = match ($analysisPeriodType) {
                'mingguan' => $startPeriod->copy()->addWeeks($analysisPeriodValue)->addYear(), // +1 year
                'bulanan' => $startPeriod->copy()->addMonths($analysisPeriodValue)->addYear(), // +1 year  
                default => $startPeriod->copy()->addYear(), // Default +1 year
            };

            $baseAttributes = [
                'rationale' => $profile['rationale'],
                'quality_dimension' => $profile['quality_dimension'],
                'objective' => $profile['objective'],
                'operational_definition' => $profile['operational_definition'],
                'indicator_type' => $indicatorType,
                'numerator_formula' => $profile['numerator_formula'],
                'denominator_formula' => $profile['denominator_formula'],
                'target_operator' => $profile['target_operator'] ?? '>=',
                'inclusion_criteria' => $profile['inclusion_criteria'],
                'exclusion_criteria' => $profile['exclusion_criteria'],
                'data_source' => $profile['data_source'],
                'data_collection_frequency' => $profile['data_collection_frequency'],
                'analysis_plan' => $profile['analysis_plan'],
                'analysis_period_type' => $analysisPeriodType,
                'analysis_period_value' => $analysisPeriodValue,
                'valid_from' => $startPeriod->format('Y-m-d'),
                'valid_until' => $endPeriod->format('Y-m-d'),
                'data_collection_method' => $profile['data_collection_method'],
                'sampling_method' => $profile['sampling_method'],
                'data_collection_tool' => $profile['data_collection_tool'],
                'responsible_person' => $profile['responsible_person'],
            ];

            // ============================================
            // >> VERSI KUARTAL DINAMIS REALISTIS <<
            // ============================================

            // ============================================
            // >> MULTIPLE QUARTERLY PROFILES FOR DEBUGGING <<
            // ============================================

            $initialTarget = (float) $profile['target_value'];
            $targetOperator = $profile['target_operator'] ?? '>=';
            $totalQuarters = $this->totalYears * 4; // 8 quarters (2025-2026)

            // Start from 2025 but ensure we have active quarters in 2026  
            $startQuarter = Carbon::create(2025, 1, 1)->startOfQuarter();
            $versionList = [];

            // Generate quarters: 2025-Q1, Q2, Q3, Q4, 2026-Q1, Q2, Q3, Q4
            for ($i = 0; $i < $totalQuarters; $i++) {
                $currentQuarter = $startQuarter->copy()->addQuarters($i);
                $q = ceil($currentQuarter->month / 3);
                $versionList[] = 'verion-' . $currentQuarter->year . '-Q' . $q;
            }

            $currentTarget = $initialTarget;
            $lastImutProfile = null;

            // Fungsi bantu: hitung step acak, tapi tetap kecil (2–8%)
            $getRandomStep = fn() => rand(2, 8);

            // Loop tiap kuartal dengan periode start/end yang tepat
            foreach ($versionList as $index => $versionKey) {
                // Extract year and quarter from version
                preg_match('/verion-(\d{4})-Q(\d)/', $versionKey, $matches);
                $year = (int) $matches[1];
                $quarter = (int) $matches[2];

                // Calculate quarter start and end dates - NON-OVERLAPPING
                $quarterStart = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
                $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay()->endOfDay(); // End 1 day before next quarter starts

                // For the last quarter (2026-Q4), extend end period to 2027
                if ($index === count($versionList) - 1) {
                    $quarterEnd = Carbon::create($year + 1, 12, 31); // End of next year
                }

                // Untuk kuartal pertama, jangan lompat jauh—beri variasi kecil
                if ($index === 0) {
                    $step = $getRandomStep();
                } else {
                    $step = $getRandomStep();
                }

                // Tentukan arah perubahan sesuai operator
                if (in_array($targetOperator, ['>=', '>'])) {
                    // kalau target awal < 100, naik perlahan
                    if ($currentTarget < 100) {
                        $currentTarget = min($currentTarget + $step, 100);
                    }
                    // kalau sudah >= 100, bisa turun sedikit (misal karena fluktuasi)
                    elseif ($currentTarget > 100) {
                        $currentTarget = max($currentTarget - $getRandomStep(), 100);
                    }
                } else {
                    // operator <= atau < : target menurun ke rendah
                    if ($currentTarget > 0) {
                        $currentTarget = max($currentTarget - $step, 0);
                    }
                    // kalau sampai 0, naik sedikit fluktuasi
                    elseif ($currentTarget < 0) {
                        $currentTarget = min($currentTarget + $getRandomStep(), 0);
                    }
                }

                // Bulatkan ke integer
                $currentTarget = round($currentTarget);

                // Siapkan attributes dengan periode yang benar
                $attributes = $baseAttributes;
                $attributes['target_value'] = $currentTarget;
                $attributes['valid_from'] = $quarterStart->format('Y-m-d');
                $attributes['valid_until'] = $quarterEnd->format('Y-m-d');

                $createdAt = $quarterStart->copy()->addDays(rand(0, 30));

                $lastImutProfile = ImutProfile::firstOrCreate([
                    'imut_data_id' => $imutData->id,
                    'version'      => $versionKey,
                ], array_merge($attributes, [
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]));

                // 🔍 LOGGING FOR KEPATUHAN KEBERSIHAN TANGAN
                if (str_contains(strtolower($indicator['title']), 'kepatuhan kebersihan tangan')) {
                    $this->command->info("📋 PROFIL KEPATUHAN KEBERSIHAN TANGAN - {$versionKey}:");
                    $this->command->line("   • ID Profile: {$lastImutProfile->id}");
                    $this->command->line("   • Version: {$versionKey}");
                    $this->command->line("   • Periode: {$quarterStart->format('Y-m-d')} → {$quarterEnd->format('Y-m-d')}");
                    $this->command->line("   • Target Value: {$currentTarget}");
                    $this->command->line("   • Valid From: {$attributes['valid_from']}");
                    $this->command->line("   • Valid Until: {$attributes['valid_until']}");
                    $this->command->line("   • ImutData ID: {$imutData->id}");

                    // Check if this profile would be selected for Jan 2026 assessment
                    $jan2026Start = Carbon::create(2026, 1, 27);
                    $jan2026End = Carbon::create(2026, 1, 31);
                    $isValidForJan2026 = $quarterStart <= $jan2026End && $quarterEnd >= $jan2026Start;

                    if ($isValidForJan2026) {
                        $this->command->comment("   ✅ VALID untuk assessment Jan 2026 (27-31 Jan)");
                    } else {
                        $this->command->comment("   ❌ TIDAK valid untuk assessment Jan 2026");
                    }
                    $this->command->line("");
                }
            }

            // Tambahkan ke unit kerja & penilaian bila perlu
            if (in_array($category->short_name, ['INM'])) {
                foreach ($this->unitKerjaIds as $unitId) {
                    $imutData->unitKerja()->syncWithoutDetaching([
                        $unitId => [
                            'assigned_by' => $this->adminUserId ?? 1,
                            'assigned_at' => now(),
                        ],
                    ]);
                }

                if ($category->is_benchmark_category) {
                    $this->createBenchmarking($imutData);
                }

                if ($lastImutProfile) {
                    $this->createPenilaian($lastImutProfile);
                }
            }
        } catch (\Throwable $e) {
            dd([
                'error' => $e->getMessage(),
                'indicator' => $indicator,
            ]);
        }
    }


    private function createBenchmarking(ImutData $imutData): void
    {
        $regionTypes = RegionType::all();

        foreach ($this->laporanList as $laporan) {
            // Periode assessment dari laporan
            $periodStart = Carbon::parse($laporan->assessment_period_start)->startOfDay();
            $periodEnd   = Carbon::parse($laporan->assessment_period_end)->endOfDay();

            // Created_at sesudah periode selesai (lebih realistis)
            $createdAt = $periodEnd->copy()->addDays(rand(0, 10));

            $benchmarkings = [];

            foreach ($regionTypes as $type) {
                // Cek kalau SUDAH ada benchmarking yang bentrok periode untuk indikator & region type ini
                if ($this->hasBenchmarkOverlap(
                    $imutData->id,
                    $type->id,
                    $periodStart,
                    $periodEnd
                )) {
                    // kalau bentrok, lewati (tidak boleh buat)
                    continue;
                }

                $benchmarkings[] = [
                    'imut_data_id'   => $imutData->id,
                    'region_type_id' => $type->id,
                    'period_start'   => $periodStart,
                    'period_end'     => $periodEnd,
                    'created_at'     => $createdAt,
                    'updated_at'     => $createdAt,
                ];
            }

            if (! empty($benchmarkings)) {
                ImutBenchmarking::insert($benchmarkings);
            }
        }
    }

    /**
     * Cek apakah sudah ada benchmarking yang overlap dengan periode baru.
     */
    private function hasBenchmarkOverlap(
        int $imutDataId,
        int $regionTypeId,
        Carbon $newStart,
        Carbon $newEnd
    ): bool {
        return ImutBenchmarking::query()
            ->where('imut_data_id', $imutDataId)
            ->where('region_type_id', $regionTypeId)
            ->where(function ($q) use ($newStart, $newEnd) {
                $q
                    // existing start di dalam range baru
                    ->whereBetween('period_start', [$newStart, $newEnd])
                    // atau existing end di dalam range baru
                    ->orWhereBetween('period_end', [$newStart, $newEnd])
                    // atau existing sepenuhnya meliputi range baru
                    ->orWhere(function ($q2) use ($newStart, $newEnd) {
                        $q2->where('period_start', '<=', $newStart)
                            ->where('period_end', '>=', $newEnd);
                    });
            })
            ->exists();
    }




    private function createPenilaian(ImutProfile $imutProfile): void
    {
        $penilaians = [];
        foreach ($this->laporanList as $laporan) {
            foreach ($this->unitKerjaIds as $unitId) {
                $pivotId = DB::table('laporan_unit_kerjas')
                    ->where('laporan_imut_id', $laporan->id)
                    ->where('unit_kerja_id', $unitId)
                    ->value('id');

                if (! $pivotId) {
                    $this->command->warn("Pivot laporan_unit_kerja tidak ditemukan untuk laporan ID $laporan->id dan unit ID $unitId");
                    continue;
                }

                $denominator = 0;
                $numerator = 0;

                if ($laporan->status !== 'coming_soon') {
                    $denominator = $this->faker->numberBetween(80, 120);
                    $numerator = $this->faker->numberBetween(
                        (int) ($denominator * 0.7),
                        $denominator
                    );
                }

                $createdAt = Carbon::create($laporan->assessment_period_end)->copy()->subDays(rand(0, 3));

                $penilaians[] = [
                    'imut_profil_id' => $imutProfile->id,
                    'laporan_unit_kerja_id' => $pivotId,
                    'analysis' => $this->faker->sentence(2),
                    'recommendations' => $this->faker->sentence(15),
                    'numerator_value' => $numerator,
                    'denominator_value' => $denominator,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
        }

        ImutPenilaian::insert($penilaians);

        // Re-enable validation after seeding
        putenv('DISABLE_IMUT_VALIDATION=false');
    }

    /**
     * Show comprehensive summary after seeding
     */
    private function showSeedingSummary(): void
    {
        $this->command->line("");
        $this->command->info("✅ SEEDING COMPLETED - RINGKASAN HASIL:");
        $this->command->line(str_repeat("=", 60));

        // Count total profiles
        $totalProfiles = ImutProfile::count();
        $totalData = ImutData::count();

        // Find Kepatuhan Kebersihan Tangan specifically
        $kebersihanData = ImutData::where('title', 'like', '%kepatuhan kebersihan tangan%')->first();

        if ($kebersihanData) {
            $kebersihanProfiles = ImutProfile::where('imut_data_id', $kebersihanData->id)->get();

            $this->command->comment("📊 RINGKASAN DATABASE:");
            $this->command->line("   • Total ImutData: {$totalData}");
            $this->command->line("   • Total ImutProfile: {$totalProfiles}");
            $this->command->line("");

            $this->command->comment("🎯 KEPATUHAN KEBERSIHAN TANGAN:");
            $this->command->line("   • ImutData ID: {$kebersihanData->id}");
            $this->command->line("   • Jumlah Profiles: " . $kebersihanProfiles->count());
            $this->command->line("");

            if ($kebersihanProfiles->isNotEmpty()) {
                $this->command->comment("📋 DAFTAR PROFILES KEBERSIHAN TANGAN:");
                foreach ($kebersihanProfiles as $profile) {
                    $this->command->line("   • ID {$profile->id}: {$profile->version} | {$profile->valid_from} → {$profile->valid_until} | Target: {$profile->target_value}");
                }
                $this->command->line("");

                // Show which profile would be selected for different assessment periods
                $this->command->comment("🔍 SIMULASI PROFILE SELECTION:");
                $testPeriods = [
                    ['2026-01-27', '2026-01-31', 'Jan 2026 Assessment'],
                    ['2026-04-27', '2026-04-30', 'Apr 2026 Assessment'],
                    ['2026-07-27', '2026-07-31', 'Jul 2026 Assessment'],
                    ['2026-10-27', '2026-10-31', 'Oct 2026 Assessment'],
                ];

                foreach ($testPeriods as [$start, $end, $label]) {
                    $selectedProfile = $kebersihanProfiles
                        ->filter(function ($profile) use ($start, $end) {
                            return $profile->valid_from <= $end &&
                                ($profile->valid_until === null || $profile->valid_until >= $start);
                        })
                        ->sortByDesc('valid_from')
                        ->first();

                    if ($selectedProfile) {
                        $this->command->line("   • {$label}: {$selectedProfile->version} (Target: {$selectedProfile->target_value})");
                    } else {
                        $this->command->line("   • {$label}: ❌ Tidak ada profile valid");
                    }
                }
            }
        }

        $this->command->line("");
        $this->command->info("🚀 READY FOR TESTING! Jalankan Job ProsesPenilaianImut untuk test.");
    }
}
