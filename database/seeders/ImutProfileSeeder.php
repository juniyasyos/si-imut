<?php

namespace Database\Seeders;

use App\Traits\ImutInitializer;
use App\Domains\Imut\Models\ImutData;
use App\Domains\Imut\Models\ImutProfile;
use App\Domains\Imut\Models\ImutCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ImutProfileSeeder extends Seeder
{
    use ImutInitializer;

    public function run(): void
    {
        $this->initImut();

        // Daftar file & kategori yang sama persis dengan ImutDataSeeder:
        $filesByCategory = [
            'INM'      => 'inm.json',
            'IMP-UNIT' => 'imp-unit.json',
            'IMP-RS'   => 'imp-rs.json',
            'IMIKP'    => 'imp_kp.json',
            'UNIT'     => 'unit.json',
        ];

        foreach ($filesByCategory as $shortName => $filename) {
            // 1. Ambil kategori
            $category = ImutCategory::where('short_name', $shortName)->first();
            if (! $category) {
                $this->command->warn("Kategori $shortName tidak ditemukan. Lewatkan profile.");
                continue;
            }

            // 2. Load JSON
            $path = database_path("data/{$filename}");
            if (! File::exists($path)) {
                $this->command->warn("File data/{$filename} tidak ada. Lewatkan profile.");
                continue;
            }
            $items = json_decode(File::get($path), true);

            // 3. Proses setiap indikator
            foreach ($items as $indicator) {
                // Cari ImutData berdasarkan judul
                $imutData = ImutData::where('title', $indicator['title'])
                    ->where('imut_kategori_id', $category->id)
                    ->first();
                if (! $imutData) {
                    $this->command->warn("ImutData \"{$indicator['title']}\" belum disimpan. Lewatkan.");
                    continue;
                }

                $profile = $indicator['profile'] ?? null;
                if (! $profile) {
                    $this->command->warn("Profile JSON tidak ada untuk \"{$indicator['title']}\".");
                    continue;
                }

                // Validasi keys (sama seperti sebelumnya)
                $required = [
                    'target_value',
                    'analysis_period_type',
                    'analysis_period_value',
                    'rationale',
                    'quality_dimension',
                    'objective',
                    'operational_definition',
                    'indicator_type',
                    'numerator_formula',
                    'denominator_formula',
                    'inclusion_criteria',
                    'exclusion_criteria',
                    'data_source',
                    'data_collection_frequency',
                    'analysis_plan',
                    'data_collection_method',
                    'sampling_method',
                    'data_collection_tool',
                    'responsible_person'
                ];
                if ($miss = array_diff($required, array_keys($profile))) {
                    $this->command->warn(
                        "Missing keys [" . implode(',', $miss) . "] pada \"{$indicator['title']}\"."
                    );
                    continue;
                }

                // Siapkan atribut dasar
                $startYear = Carbon::now()->startOfYear();
                $apType     = $profile['analysis_period_type'];
                $apValue    = (int) $profile['analysis_period_value'];
                $endYear    = match ($apType) {
                    'mingguan' => $startYear->copy()->addWeeks($apValue),
                    'bulanan'  => $startYear->copy()->addMonths($apValue),
                    default    => $startYear->copy(),
                };

                $base = [
                    'rationale'                  => $profile['rationale'],
                    'quality_dimension'          => $profile['quality_dimension'],
                    'objective'                  => $profile['objective'],
                    'operational_definition'     => $profile['operational_definition'],
                    'indicator_type'             => in_array($profile['indicator_type'], ['process', 'outcome', 'output'])
                        ? $profile['indicator_type']
                        : 'process',
                    'numerator_formula'          => $profile['numerator_formula'],
                    'denominator_formula'        => $profile['denominator_formula'],
                    'target_operator'            => $profile['target_operator'] ?? '>=',
                    'inclusion_criteria'         => $profile['inclusion_criteria'],
                    'exclusion_criteria'         => $profile['exclusion_criteria'],
                    'data_source'                => $profile['data_source'],
                    'data_collection_frequency'  => $profile['data_collection_frequency'],
                    'analysis_plan'              => $profile['analysis_plan'],
                    'analysis_period_type'       => $apType,
                    'analysis_period_value'      => $apValue,
                    'start_period'               => $startYear->format('Y-m-d'),
                    'end_period'                 => $endYear->format('Y-m-d'),
                    'data_collection_method'     => $profile['data_collection_method'],
                    'sampling_method'            => $profile['sampling_method'],
                    'data_collection_tool'       => $profile['data_collection_tool'],
                    'responsible_person'         => $profile['responsible_person'],
                ];

                // 4. Buat versi per kuartal seperti logika original
                $totalQ  = $this->totalYears * 4;
                $current = (float) $profile['target_value'];
                $stepFn  = fn() => rand(2, 8);
                $startQ  = Carbon::now()
                    ->copy()
                    ->subYears($this->totalYears)
                    ->startOfYear()
                    ->startOfQuarter();

                for ($i = 0; $i < $totalQ; $i++) {
                    $qnum = ceil($startQ->month / 3);
                    $version = $startQ->year . "-Q{$qnum}";

                    // adjust target
                    $step = $stepFn();
                    if (in_array($base['target_operator'], ['>=', '>'])) {
                        $current = min($current + $step, 100);
                    } else {
                        $current = max($current - $step, 0);
                    }
                    $current = round($current);

                    ImutProfile::firstOrCreate(
                        [
                            'imut_data_id' => $imutData->id,
                            'version'      => $version,
                        ],
                        array_merge($base, ['target_value' => $current])
                    );

                    $startQ->addQuarter();
                }
            }
        }
    }
}