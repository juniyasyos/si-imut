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
                // ShieldSeeder::class,
                // UserSeeder::class,
                // AssignSuperAdminRoleSeeder::class,
                // KaidoSettingSeeder::class,
                // UnitKerjaSeeder::class,
                // RoleUpgradeSeeder::class,
                // ImutCategorySeeder::class,
                // RegionTypeSeeder::class,
                // ImutDataOldSeeder::class,

                // Step 1: Create FormTemplates with JSON configs for all profiles
                // CompleteFormTemplateSeeder::class,

                // Step 2: Replicate expired/future profiles to make them currently valid
                // ValidDailyReportProfileSeeder::class,

                // Step 3: Create default FormTemplates for profiles without JSON configs
                // DefaultFormTemplateSeeder::class,

                // Step 4: Create simulation data for specific indicators
                // HandwashingSimulationSeeder::class,

                // Step 5: Create sample daily report entries for testing
                // SampleDailyReportSeeder::class,

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
