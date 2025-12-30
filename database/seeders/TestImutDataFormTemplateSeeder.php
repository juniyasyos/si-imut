<?php

namespace Database\Seeders;

use App\Models\ImutData;
use App\Models\ImutCategory;
use App\Models\ImutProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestImutDataFormTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧪 Testing automatic FormTemplate creation on ImutProfile...');

        // Get or create test category
        $category = ImutCategory::firstOrCreate(
            ['short_name' => 'TEST'],
            [
                'category_name' => 'Test Category',
                'description' => 'Category for testing FormTemplate auto-creation',
                'scope' => 'internal',
            ]
        );

        // Get admin user for created_by
        $admin = User::where('name', 'admin')->first();
        if (!$admin) {
            $admin = User::first();
        }

        $testCases = [
            [
                'title' => 'Kepatuhan Penggunaan APD Test',
                'description' => 'Test case - should match APD JSON config'
            ],
            [
                'title' => 'Audit Cuci Tangan Test',
                'description' => 'Test case - should match hand hygiene JSON config'
            ],
            [
                'title' => 'Pencegahan Risiko Jatuh Test',
                'description' => 'Test case - should match fall prevention JSON config'
            ],
            [
                'title' => 'Identifikasi Pasien Test',
                'description' => 'Test case - should match patient identification JSON config'
            ],
            [
                'title' => 'Random Indicator Test',
                'description' => 'Test case - should create default Yes/No template'
            ],
        ];

        foreach ($testCases as $testCase) {
            // Check if already exists
            $existing = ImutData::where('title', $testCase['title'])->first();
            if ($existing) {
                $this->command->warn("⚠️  ImutData '{$testCase['title']}' already exists, skipping...");
                continue;
            }

            $this->command->info("Creating ImutData: {$testCase['title']}");

            // Create ImutData - then create ImutProfile which will auto-create FormTemplate
            $imutData = ImutData::create([
                'title' => $testCase['title'],
                'description' => $testCase['description'],
                'imut_kategori_id' => $category->id,
                'created_by' => $admin->id,
                'status' => true,
            ]);

            $this->command->info("✅ Created ImutData ID: {$imutData->id}");

            // Create ImutProfile - observer will automatically create FormTemplate
            $imutProfile = ImutProfile::create([
                'imut_data_id' => $imutData->id,
                'version' => 'v1.0',
                'valid_from' => now()->toDateString(),
                'valid_until' => now()->addMonths(6)->toDateString(),
                'rationale' => 'Test profile for ' . $imutData->title,
                'objective' => 'Test objective',
                'operational_definition' => 'Test definition',
                'indicator_type' => 'process',
                'numerator_formula' => 'Test formula',
                'denominator_formula' => 'Test formula',
                'data_source' => 'Test source',
                'data_collection_frequency' => 'monthly',
                'analysis_plan' => 'Test plan',
                'target_operator' => '>=',
                'target_value' => 80,
                'responsible_person' => 'Test Person',
            ]);

            $this->command->info("✅ Created ImutProfile ID: {$imutProfile->id}");

            // Check if FormTemplate was created
            $formTemplates = $imutProfile->formTemplates;
            if ($formTemplates->count() > 0) {
                $this->command->info("✅ FormTemplate auto-created: {$formTemplates->first()->title}");
                $this->command->info("   - Fields count: {$formTemplates->first()->formFields->count()}");
            } else {
                $this->command->error("❌ No FormTemplate created for: {$imutData->title}");
            }
        }

        $this->command->info('🎉 Test completed!');
    }
}
