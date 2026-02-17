<?php

namespace Database\Seeders;

use App\Models\ImutCategory;
use App\Models\ImutData;
use App\Models\ImutProfile;
use Illuminate\Database\Seeder;

class CreateHandHygieneProfileSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔧 Creating ImutCategory/ImutData/ImutProfile for Kepatuhan Kebersihan Tangan');

        $category = ImutCategory::firstOrCreate(
            ['short_name' => 'INM'],
            [
                'category_name' => 'Indikator Mutu Nasional',
                'scope' => 'national',
                'description' => 'Kategori default untuk IMN',
            ]
        );

        $admin = \App\Models\User::first();

        $imutData = ImutData::firstOrCreate(
            ['title' => 'Kepatuhan Kebersihan Tangan'],
            [
                'imut_kategori_id' => $category->id,
                'description' => 'Test: Kepatuhan Kebersihan Tangan',
                'status' => true,
                'created_by' => $admin?->id ?? 1,
            ]
        );

        $profile = ImutProfile::firstOrCreate(
            [
                'imut_data_id' => $imutData->id,
                'version' => 'v1-test',
            ],
            [
                'valid_from' => now()->subDays(1)->toDateString(),
                'valid_until' => now()->addYear()->toDateString(),
                'rationale' => 'Test profile for hand hygiene',
                'objective' => 'Test objective',
                'operational_definition' => 'Test operational definition',
                'indicator_type' => 'process',
                'numerator_formula' => 'num',
                'denominator_formula' => 'den',
                'data_source' => 'observation',
                'data_collection_frequency' => 'daily',
                'analysis_plan' => 'Test plan',
                'target_operator' => '>=',
                'target_value' => 80,
                'responsible_person' => 'QA Team',
            ]
        );

        $this->command->info("✅ Created ImutProfile ID: {$profile->id} for ImutData ID: {$imutData->id}");
    }
}
