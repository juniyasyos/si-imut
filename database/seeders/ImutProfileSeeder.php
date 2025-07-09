<?php

namespace Database\Seeders;

use App\Traits\ImutInitializer;
use App\Models\ImutData;
use App\Models\ImutProfile;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ImutProfileSeeder extends Seeder
{
    use ImutInitializer;

    public function run(): void
    {
        $this->initImut();

        ImutData::with('category')->get()->each(function ($d) {
            $profile = json_decode($d->description, true)['profile'] ?? null;
            if (! $profile) {
                $this->command->warn("Tidak ada profile untuk {$d->title}");
                return;
            }

            // validasi keys
            $required = [
                'target_value',
                'target_operator',
                'analysis_period_type',
                'analysis_period_value',
                'rationale',
                'quality_dimension',
                'objective',
                'operational_definition',
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
                $this->command->warn("Missing keys: " . implode(',', $miss) . " pada {$d->title}");
                return;
            }

            // prepare base attrs
            $startYear = now()->startOfYear();
            $apType  = $profile['analysis_period_type'];
            $apValue = (int)$profile['analysis_period_value'];
            $endYear = match ($apType) {
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

            // buat versi kuartal
            $totalQ = $this->totalYears * 4;
            $initT  = (float)$profile['target_value'];
            $startQ = now()->copy()->subYears($this->totalYears)->startOfYear()->startOfQuarter();
            $curT   = $initT;
            $vers  = [];
            for ($i = 0; $i < $totalQ; $i++) {
                $q = ceil($startQ->month / 3);
                $vers[] = "{$startQ->year}-Q{$q}";
                $startQ->addQuarter();
            }

            foreach ($vers as $idx => $v) {
                $step = rand(2, 8);
                if (in_array($base['target_operator'], ['>=', '>'])) {
                    $curT = min($curT + $step, 100);
                } else {
                    $curT = max($curT - $step, 0);
                }
                $curT = round($curT);
                ImutProfile::firstOrCreate(
                    ['imut_data_id' => $d->id, 'version' => $v],
                    array_merge($base, ['target_value' => $curT])
                );
            }
        });
    }
}
