<?php

namespace Database\Seeders;

use Database\Seeders\UserSeeder;
use Database\Seeders\ImutDataSeeder;
use Database\Seeders\CompleteFormTemplateSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                // KaidoSettingSeeder::class,
                // ShieldSeeder::class,
                // UserSeeder::class,
                // UnitKerjaSeeder::class,
                RoleUpgradeSeeder::class,
                // ImutCategorySeeder::class,
                // RegionTypeSeeder::class,
                // ImutDataOldSeeder::class,

                // Step 1: Create FormTemplates with JSON configs for all profiles
                CompleteFormTemplateSeeder::class,

                // Step 2: Replicate expired/future profiles to make them currently valid
                ValidDailyReportProfileSeeder::class, // DISABLED: Creates duplicates, profiles already valid from CompleteFormTemplateSeeder

                // Step 3: Create simulation data for specific indicators
                HandwashingSimulationSeeder::class,

                // Note: EnsureFormTemplateSeeder removed - redundant with CompleteFormTemplateSeeder

                // ImutDataOldSeederOptimized::class, // Using optimized version
                // HandHygieneFormSeeder::class,
                // EnhancedFormBuilderSeeder::class,
                // LaporanImutSeeder::class,
                // ImutDataSeeder::class,
                // ImutProfileSeeder::class,
                // ImutBenchmarkingSeeder::class,
                // ImutPenilaianSeeder::class,
            ]
        );
    }
}
