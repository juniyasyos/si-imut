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

class ImutDataOldSeederOptimized extends Seeder
{
    protected $faker;
    protected $now;
    protected $adminUserId;
    protected $unitKerjaIds;
    protected $laporanList = [];
    protected $regionTypes;
    protected int $totalYears = 2;
    protected int $batchSize = 500; // Batch size untuk insert

    public function run(): void
    {
        $this->init();

        $filesByCategoryShortName = [
            'INM' => 'inm.json',
            'IMP-UNIT' => 'imp-unit.json',
            'IMP-RS' => 'imp-rs.json',
            'IMIKP' => 'imp_kp.json',
            'UNIT' => 'unit.json'
        ];

        $this->createLaporanImut();

        foreach ($filesByCategoryShortName as $shortName => $filename) {
            $this->command->info("Processing category: $shortName");

            $category = ImutCategory::where('short_name', $shortName)->first();
            if (!$category) {
                $this->command->warn("Kategori \"$shortName\" tidak ditemukan.");
                continue;
            }

            $indicators = $this->getJsonData($filename);
            if (!$indicators) {
                continue;
            }

            $this->processIndicatorsOptimized($indicators, $category);
        }

        $this->command->info('✅ Seeding completed successfully!');
    }

    private function init(): void
    {
        $this->faker = Faker::create();
        $this->now = Carbon::now();
        $this->adminUserId = User::where('name', 'admin')->value('id') ?? 1;
        $this->unitKerjaIds = UnitKerja::pluck('id')->toArray();
        $this->regionTypes = RegionType::all();

        // Disable query log to save memory
        DB::disableQueryLog();
    }

    private function getJsonData(string $filename): ?array
    {
        $filePath = database_path("data/$filename");

        if (!File::exists($filePath)) {
            $this->command->warn("File \"$filename\" tidak ditemukan.");
            return null;
        }

        return json_decode(File::get($filePath), true);
    }

    private function createLaporanImut(): void
    {
        $totalMonths = $this->totalYears * 12;
        $laporanData = [];
        $laporanUnitKerjaData = [];

        for ($i = 0; $i < $totalMonths; $i++) {
            $month = $this->now->copy()->subMonths($i)->month;
            $year = $this->now->copy()->subMonths($i)->year;

            $start = Carbon::create($year, $month, 1);
            $end = $start->copy()->endOfMonth();
            $assessmentStart = $end->copy()->subDays(4);

            // Generate unique slug for batch insert (since Eloquent events won't fire)
            $periodSlug = $year . str_pad($month, 2, '0', STR_PAD_LEFT);
            $uniqueId = substr(str_replace('-', '', \Illuminate\Support\Str::uuid()), 0, 8);
            $slug = 'laporan-imut-' . $periodSlug . '-' . $uniqueId;

            $laporanData[] = [
                'name' => "Laporan IMUT Periode $month/$year",
                'slug' => $slug,
                'report_month' => $month,
                'report_year' => $year,
                'assessment_period_start' => $assessmentStart,
                'assessment_period_end' => $end,
                'status' => LaporanImut::STATUS_PROCESS,
                'created_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Check for existing laporans first, create only if needed
        $existingLaporans = LaporanImut::whereIn('name', array_column($laporanData, 'name'))->get();

        if ($existingLaporans->count() > 0) {
            $this->command->info("Found {$existingLaporans->count()} existing laporans, using them instead of creating new ones.");
            $this->laporanList = $existingLaporans;
        } else {
            // Batch insert laporan only if none exist
            collect($laporanData)->chunk($this->batchSize)->each(function ($chunk) {
                LaporanImut::insert($chunk->toArray());
            });

            // Get created laporans
            $this->laporanList = LaporanImut::latest()->take($totalMonths)->get();
        }

        // Batch insert laporan_unit_kerja relations only if laporans were newly created
        if ($existingLaporans->count() === 0) {
            foreach ($this->laporanList as $laporan) {
                foreach ($this->unitKerjaIds as $unitKerjaId) {
                    $laporanUnitKerjaData[] = [
                        'laporan_imut_id' => $laporan->id,
                        'unit_kerja_id' => $unitKerjaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($laporanUnitKerjaData)) {
                collect($laporanUnitKerjaData)->chunk($this->batchSize)->each(function ($chunk) {
                    LaporanUnitKerja::insert($chunk->toArray());
                });
            }
        } else {
            $this->command->info("Skipping laporan unit kerja relations as they likely already exist.");
        }
    }

    private function processIndicatorsOptimized(array $indicators, ImutCategory $category): void
    {
        $imutDataBatch = [];
        $imutProfilesBatch = [];
        $benchmarkingBatch = [];
        $penilaianBatch = [];
        $unitKerjaRelations = [];

        // Prepare data for batch insert
        foreach ($indicators as $index => $indicator) {
            try {
                // Generate ImutData
                $imutDataId = $index + 1000 + ($category->id * 10000); // Unique ID generation

                $imutDataBatch[] = [
                    'id' => $imutDataId,
                    'title' => $indicator['title'],
                    'imut_kategori_id' => $category->id,
                    'description' => $indicator['description'],
                    'status' => true,
                    'created_by' => $this->adminUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Generate ImutProfiles for quarters
                $profiles = $this->generateProfilesForIndicator($imutDataId, $indicator['profile']);
                $imutProfilesBatch = array_merge($imutProfilesBatch, $profiles);

                // Generate benchmarkings if needed
                if ($category->is_benchmark_category) {
                    $benchmarks = $this->generateBenchmarkingForIndicator($imutDataId);
                    $benchmarkingBatch = array_merge($benchmarkingBatch, $benchmarks);
                }

                // Generate penilaians
                $lastProfile = end($profiles);
                if ($lastProfile) {
                    $penilaians = $this->generatePenilaianForProfile($lastProfile['id']);
                    $penilaianBatch = array_merge($penilaianBatch, $penilaians);
                }

                // Generate unit kerja relations for INM
                if ($category->short_name === 'INM') {
                    foreach ($this->unitKerjaIds as $unitId) {
                        $unitKerjaRelations[] = [
                            'imut_data_id' => $imutDataId,
                            'unit_kerja_id' => $unitId,
                            'assigned_by' => $this->adminUserId,
                            'assigned_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                $this->command->error("Error processing indicator: " . $indicator['title'] . " - " . $e->getMessage());
                continue;
            }
        }

        // Batch insert all data
        $this->batchInsertData([
            'imut_data' => $imutDataBatch,
            'imut_profiles' => $imutProfilesBatch,
            'benchmarkings' => $benchmarkingBatch,
            'penilaians' => $penilaianBatch,
            'unit_kerja_relations' => $unitKerjaRelations,
        ]);

        $this->command->info("✅ Processed " . count($indicators) . " indicators for category: " . $category->short_name);
    }

    private function generateProfilesForIndicator(int $imutDataId, array $profileData): array
    {
        $profiles = [];
        $totalQuarters = $this->totalYears * 4;
        $startQuarter = Carbon::create(2025, 1, 1)->startOfQuarter();

        $initialTarget = (float) ($profileData['target_value'] ?? 80);
        $targetOperator = $profileData['target_operator'] ?? '>=';
        $currentTarget = $initialTarget;

        for ($i = 0; $i < $totalQuarters; $i++) {
            $currentQuarter = $startQuarter->copy()->addQuarters($i);
            $q = ceil($currentQuarter->month / 3);
            $versionKey = 'version-' . $currentQuarter->year . '-Q' . $q;

            $quarterStart = Carbon::create($currentQuarter->year, ($q - 1) * 3 + 1, 1)->startOfMonth();
            $quarterEnd = $quarterStart->copy()->addMonths(3)->endOfMonth();

            if ($i === $totalQuarters - 1) {
                $quarterEnd = Carbon::create($currentQuarter->year + 1, 12, 31);
            }

            // Adjust target with small random steps
            $step = rand(2, 8);
            if (in_array($targetOperator, ['>=', '>'])) {
                $currentTarget = $currentTarget < 100 ? min($currentTarget + $step, 100) : max($currentTarget - $step, 100);
            } else {
                $currentTarget = $currentTarget > 0 ? max($currentTarget - $step, 0) : min($currentTarget + $step, 0);
            }

            $profileId = ($imutDataId * 100) + $i; // Generate unique profile ID

            $profiles[] = [
                'id' => $profileId,
                'imut_data_id' => $imutDataId,
                'version' => $versionKey,
                'rationale' => $profileData['rationale'] ?? 'Default rationale',
                'quality_dimension' => $profileData['quality_dimension'] ?? 'Effectiveness',
                'objective' => $profileData['objective'] ?? 'Default objective',
                'operational_definition' => $profileData['operational_definition'] ?? 'Default definition',
                'indicator_type' => in_array($profileData['indicator_type'] ?? 'process', ['process', 'outcome', 'output']) ? $profileData['indicator_type'] : 'process',
                'numerator_formula' => $profileData['numerator_formula'] ?? 'Default numerator',
                'denominator_formula' => $profileData['denominator_formula'] ?? 'Default denominator',
                'target_value' => round($currentTarget),
                'target_operator' => $targetOperator,
                'inclusion_criteria' => $profileData['inclusion_criteria'] ?? 'Default inclusion',
                'exclusion_criteria' => $profileData['exclusion_criteria'] ?? 'Default exclusion',
                'data_source' => $profileData['data_source'] ?? 'Default source',
                'data_collection_frequency' => $profileData['data_collection_frequency'] ?? 'Monthly',
                'analysis_plan' => $profileData['analysis_plan'] ?? 'Default analysis',
                'analysis_period_type' => $profileData['analysis_period_type'] ?? 'bulanan',
                'analysis_period_value' => (int) ($profileData['analysis_period_value'] ?? 1),
                'start_period' => $quarterStart->format('Y-m-d'),
                'end_period' => $quarterEnd->format('Y-m-d'),
                'data_collection_method' => $profileData['data_collection_method'] ?? 'Default method',
                'sampling_method' => $profileData['sampling_method'] ?? 'Default sampling',
                'data_collection_tool' => $profileData['data_collection_tool'] ?? 'Default tool',
                'responsible_person' => $profileData['responsible_person'] ?? 'Default person',
                'created_at' => $quarterStart->copy()->addDays(rand(0, 30)),
                'updated_at' => now(),
            ];
        }

        return $profiles;
    }

    private function generateBenchmarkingForIndicator(int $imutDataId): array
    {
        $benchmarks = [];

        foreach ($this->laporanList as $laporan) {
            $periodStart = Carbon::parse($laporan->assessment_period_start)->startOfDay();
            $periodEnd = Carbon::parse($laporan->assessment_period_end)->endOfDay();
            $createdAt = $periodEnd->copy()->addDays(rand(0, 10));

            foreach ($this->regionTypes as $type) {
                $benchmarks[] = [
                    'imut_data_id' => $imutDataId,
                    'region_type_id' => $type->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
        }

        return $benchmarks;
    }

    private function generatePenilaianForProfile(int $profileId): array
    {
        $penilaians = [];
        $laporanUnitKerjas = DB::table('laporan_unit_kerjas')->get();

        foreach ($laporanUnitKerjas as $pivotRecord) {
            $denominator = $this->faker->numberBetween(80, 120);
            $numerator = $this->faker->numberBetween((int)($denominator * 0.7), $denominator);

            $laporan = $this->laporanList->firstWhere('id', $pivotRecord->laporan_imut_id);
            $createdAt = $laporan ? Carbon::parse($laporan->assessment_period_end)->subDays(rand(0, 3)) : now();

            $penilaians[] = [
                'imut_profil_id' => $profileId,
                'laporan_unit_kerja_id' => $pivotRecord->id,
                'analysis' => $this->faker->sentence(2),
                'recommendations' => $this->faker->sentence(15),
                'numerator_value' => $numerator,
                'denominator_value' => $denominator,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        return $penilaians;
    }

    private function batchInsertData(array $data): void
    {
        DB::transaction(function () use ($data) {
            // Insert ImutData
            if (!empty($data['imut_data'])) {
                try {
                    // Check for existing ImutData to avoid duplicates
                    $titles = array_column($data['imut_data'], 'title');
                    $existingTitles = ImutData::whereIn('title', $titles)->pluck('title')->toArray();

                    if (!empty($existingTitles)) {
                        $this->command->warn("Found " . count($existingTitles) . " existing ImutData records, skipping duplicates");
                        $data['imut_data'] = array_filter($data['imut_data'], function ($item) use ($existingTitles) {
                            return !in_array($item['title'], $existingTitles);
                        });
                    }

                    if (!empty($data['imut_data'])) {
                        collect($data['imut_data'])->chunk($this->batchSize)->each(function ($chunk) {
                            ImutData::insert($chunk->toArray());
                        });
                        $this->command->info("✅ Inserted " . count($data['imut_data']) . " ImutData records");
                    }
                } catch (\Exception $e) {
                    $this->command->error("Error inserting ImutData: " . $e->getMessage());
                    throw $e;
                }
            }

            // Insert ImutProfiles
            if (!empty($data['imut_profiles'])) {
                try {
                    collect($data['imut_profiles'])->chunk($this->batchSize)->each(function ($chunk) {
                        ImutProfile::insert($chunk->toArray());
                    });
                    $this->command->info("✅ Inserted " . count($data['imut_profiles']) . " ImutProfile records");
                } catch (\Exception $e) {
                    $this->command->error("Error inserting ImutProfiles: " . $e->getMessage());
                    throw $e;
                }
            }

            // Insert Benchmarkings
            if (!empty($data['benchmarkings'])) {
                try {
                    collect($data['benchmarkings'])->chunk($this->batchSize)->each(function ($chunk) {
                        ImutBenchmarking::insert($chunk->toArray());
                    });
                    $this->command->info("✅ Inserted " . count($data['benchmarkings']) . " Benchmarking records");
                } catch (\Exception $e) {
                    $this->command->error("Error inserting Benchmarkings: " . $e->getMessage());
                    // Continue with other inserts even if benchmarking fails
                }
            }

            // Insert Penilaians
            if (!empty($data['penilaians'])) {
                try {
                    collect($data['penilaians'])->chunk($this->batchSize)->each(function ($chunk) {
                        ImutPenilaian::insert($chunk->toArray());
                    });
                    $this->command->info("✅ Inserted " . count($data['penilaians']) . " Penilaian records");
                } catch (\Exception $e) {
                    $this->command->error("Error inserting Penilaians: " . $e->getMessage());
                    // Continue with other inserts
                }
            }

            // Insert Unit Kerja Relations
            if (!empty($data['unit_kerja_relations'])) {
                try {
                    collect($data['unit_kerja_relations'])->chunk($this->batchSize)->each(function ($chunk) {
                        DB::table('imut_data_unit_kerja')->insert($chunk->toArray());
                    });
                    $this->command->info("✅ Inserted " . count($data['unit_kerja_relations']) . " Unit Kerja relations");
                } catch (\Exception $e) {
                    $this->command->error("Error inserting Unit Kerja relations: " . $e->getMessage());
                    // Continue even if relations fail
                }
            }
        });
    }
}
