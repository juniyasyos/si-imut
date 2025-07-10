<?php

namespace Database\Seeders;

use App\Models\ImutCategory;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImutDataProdSeeder extends Seeder
{
    protected $now;

    protected $adminUserId;

    protected $unitKerjaIds;

    protected $category;

    protected $laporanList = [];

    public function run(): void
    {
        DB::transaction(function () {
            $this->init();

            $filesByCategoryShortName = [
                'INM' => 'inm.json',
                'IMP-UNIT' => 'imp-unit.json',
                'IMP-RS' => 'imp-rs.json',
                'IMIKP' => 'imp_kp.json',
                'UNIT' => 'unit.json'
            ];

            // $this->createLaporanImut();

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

                foreach ($indicators as $indicator) {
                    $this->processIndicator($indicator, $category);
                }
            }
        });
    }

    private function init(): void
    {
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

    private function processIndicator(array $indicator, ImutCategory $category): void
    {
        try {
            $imutData = ImutData::firstOrCreate([
                'title' => $indicator['title'],
                'imut_kategori_id' => $category->id,
                'description' => $indicator['description'],
                'status' => true,
                'created_by' => $this->adminUserId,
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

            foreach ($requiredKeys as $key) {
                if (! array_key_exists($key, $profile)) {
                    throw new \Exception("Missing key in profile: '$key'");
                }
            }

            $indicatorType = in_array($profile['indicator_type'], ['process', 'outcome', 'output'])
                ? $profile['indicator_type']
                : 'process';

            $analysisPeriodType = $profile['analysis_period_type'];
            $analysisPeriodValue = (int) $profile['analysis_period_value'];

            $start_periode = Carbon::now()->startOfYear();
            $end_periode = match ($analysisPeriodType) {
                'mingguan' => $start_periode->copy()->addWeeks($analysisPeriodValue),
                'bulanan' => $start_periode->copy()->addMonths($analysisPeriodValue),
                'semester' => $start_periode->copy()->addMonths($analysisPeriodValue * 6),
                default => $start_periode->copy(),
            };

            $imutProfile = ImutProfile::firstOrCreate([
                'imut_data_id' => $imutData->id,
                'version' => 'version 1',
            ], [
                'rationale' => $profile['rationale'],
                'quality_dimension' => $profile['quality_dimension'],
                'objective' => $profile['objective'],
                'operational_definition' => $profile['operational_definition'],
                'indicator_type' => $indicatorType,
                'numerator_formula' => $profile['numerator_formula'],
                'denominator_formula' => $profile['denominator_formula'],
                'target_operator' => $profile['target_operator'] ?? '>=',
                'target_value' => $profile['target_value'],
                'inclusion_criteria' => $profile['inclusion_criteria'],
                'exclusion_criteria' => $profile['exclusion_criteria'],
                'data_source' => $profile['data_source'],
                'data_collection_frequency' => $profile['data_collection_frequency'],
                'analysis_plan' => $profile['analysis_plan'],
                'analysis_period_type' => $analysisPeriodType,
                'analysis_period_value' => $analysisPeriodValue,
                'start_period' => $start_periode->format('Y-m-d'),
                'end_period' => $end_periode->format('Y-m-d'),
                'data_collection_method' => $profile['data_collection_method'],
                'sampling_method' => $profile['sampling_method'],
                'data_collection_tool' => $profile['data_collection_tool'],
                'responsible_person' => $profile['responsible_person'],
            ]);
        } catch (\Throwable $e) {
            dd([
                'error' => $e->getMessage(),
                'indicator' => $indicator,
            ]);
        }
    }
}