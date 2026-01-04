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

            collect($indicators)->chunk(50)->each(function ($chunkedIndicators) use ($category) {
                foreach ($chunkedIndicators as $indicator) {
                    $this->processIndicator($indicator, $category);
                }
            });
        }
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
            // >> SINGLE PROFILE FOR FUTURE USE (2025-2026) <<
            // ============================================

            $initialTarget = (float) $profile['target_value'];

            // Create single profile covering both 2025-2026
            $profileVersion = 'version-2025-2026';
            $profileValidFrom = Carbon::create(2025, 1, 1);
            $profileValidUntil = Carbon::create(2026, 12, 31);

            // Siapkan attributes dengan periode yang benar
            $attributes = $baseAttributes;
            $attributes['target_value'] = $initialTarget;
            $attributes['valid_from'] = $profileValidFrom->format('Y-m-d');
            $attributes['valid_until'] = $profileValidUntil->format('Y-m-d');

            $createdAt = now();

            $lastImutProfile = ImutProfile::firstOrCreate([
                'imut_data_id' => $imutData->id,
                'version'      => $profileVersion,
            ], array_merge($attributes, [
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]));

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
}
