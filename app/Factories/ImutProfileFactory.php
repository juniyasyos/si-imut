<?php

namespace App\Factories;

use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ImutProfileFactory extends BaseModelFactory
{
    public function create(array $attributes = []): ImutProfile
    {
        $attributes = $this->validateAttributes($attributes);
        $attributes = $this->applyDefaults($attributes);

        $profile = ImutProfile::create($attributes);

        return $this->afterCreate($profile);
    }

    protected function validateAttributes(array $attributes): array
    {
        // Ensure required fields
        if (empty($attributes['imut_data_id'])) {
            throw new \InvalidArgumentException('ImutData ID is required');
        }

        if (empty($attributes['version'])) {
            throw new \InvalidArgumentException('Profile version is required');
        }

        // Verify ImutData exists
        if (!ImutData::find($attributes['imut_data_id'])) {
            throw new \InvalidArgumentException('ImutData not found');
        }

        return $attributes;
    }

    protected function getDefaults(): array
    {
        return [
            'rationale' => 'Rasional profil IMUT standar',
            'quality_dimension' => 'Mutu',
            'objective' => 'Meningkatkan kualitas pelayanan',
            'operational_definition' => 'Definisi operasional standar',
            'indicator_type' => 'process',
            'numerator_formula' => 'Jumlah yang memenuhi kriteria',
            'denominator_formula' => 'Total jumlah yang dinilai',
            'inclusion_criteria' => 'Kriteria inklusi standar',
            'exclusion_criteria' => 'Kriteria eksklusi standar',
            'data_source' => 'Sistem informasi rumah sakit',
            'data_collection_frequency' => 'Bulanan',
            'analysis_plan' => 'Analisis trend dan perbandingan dengan target',
            'target_operator' => '>=',
            'target_value' => 80,
            'analysis_period_type' => 'bulanan',
            'analysis_period_value' => 1,
            'data_collection_method' => 'Observasi dan dokumentasi',
            'sampling_method' => 'Total sampling',
            'data_collection_tool' => 'Checklist dan form evaluasi',
            'responsible_person' => Auth::user()?->name ?? 'Tim Mutu',
        ];
    }

    protected function afterCreate($profile): ImutProfile
    {
        // Create initial benchmarking data if needed
        $this->createDefaultBenchmarking($profile);

        return $profile;
    }

    /**
     * Create profile with template based on category
     */
    public function createWithTemplate(int $imutDataId, string $version, string $template = 'default'): ImutProfile
    {
        $templates = $this->getTemplates();
        $templateData = $templates[$template] ?? $templates['default'];

        return $this->create(array_merge($templateData, [
            'imut_data_id' => $imutDataId,
            'version' => $version,
        ]));
    }

    /**
     * Create profile for specific indicator type
     */
    public function createForIndicatorType(int $imutDataId, string $version, string $indicatorType): ImutProfile
    {
        $typeDefaults = $this->getIndicatorTypeDefaults($indicatorType);

        return $this->create(array_merge($typeDefaults, [
            'imut_data_id' => $imutDataId,
            'version' => $version,
            'indicator_type' => $indicatorType,
        ]));
    }

    /**
     * Get predefined templates
     */
    private function getTemplates(): array
    {
        return [
            'default' => [],
            'safety' => [
                'quality_dimension' => 'Keselamatan Pasien',
                'objective' => 'Meningkatkan keselamatan pasien',
                'target_value' => 95,
                'analysis_plan' => 'Analisis insiden keselamatan pasien dan tindakan perbaikan',
            ],
            'clinical' => [
                'quality_dimension' => 'Efektivitas Klinis',
                'objective' => 'Meningkatkan efektivitas pelayanan klinis',
                'target_value' => 85,
                'analysis_plan' => 'Evaluasi outcome klinis dan protokol tata laksana',
            ],
            'efficiency' => [
                'quality_dimension' => 'Efisiensi',
                'objective' => 'Meningkatkan efisiensi operasional',
                'target_value' => 90,
                'analysis_plan' => 'Analisis waktu tunggu dan utilisasi sumber daya',
            ],
        ];
    }

    /**
     * Get defaults based on indicator type
     */
    private function getIndicatorTypeDefaults(string $type): array
    {
        $defaults = [
            'process' => [
                'numerator_formula' => 'Jumlah proses yang dilaksanakan sesuai standar',
                'denominator_formula' => 'Total jumlah proses yang dinilai',
                'target_value' => 100,
            ],
            'output' => [
                'numerator_formula' => 'Jumlah output yang mencapai target',
                'denominator_formula' => 'Total output yang dihasilkan',
                'target_value' => 85,
            ],
            'outcome' => [
                'numerator_formula' => 'Jumlah outcome yang sesuai harapan',
                'denominator_formula' => 'Total pasien/kasus yang dinilai',
                'target_value' => 80,
            ],
        ];

        return $defaults[$type] ?? [];
    }

    /**
     * Create default benchmarking data
     */
    private function createDefaultBenchmarking(ImutProfile $profile): void
    {
        // This could create regional benchmarking data
        // Implementation depends on business requirements
    }
}
