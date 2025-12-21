<?php

namespace Database\Seeders;

use App\Models\ImutData;
use App\Models\ImutCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestImutDataFormTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧪 Testing automatic FormTemplate creation...');

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

            // Create ImutData - observer will automatically create FormTemplate
            $imutData = ImutData::create([
                'title' => $testCase['title'],
                'description' => $testCase['description'],
                'imut_kategori_id' => $category->id,
                'created_by' => $admin->id,
                'status' => true,
            ]);

            $this->command->info("✅ Created ImutData ID: {$imutData->id}");

            // Check if FormTemplate was created
            $formTemplates = $imutData->formTemplates;
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
