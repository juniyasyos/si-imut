<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ImutDataSeeder;
use Database\Seeders\CompleteFormTemplateSeeder;

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
                // RoleUpgradeSeeder::class,
                // ImutCategorySeeder::class,
                // RegionTypeSeeder::class,
                // ImutDataOldSeeder::class,
                CompleteFormTemplateSeeder::class, // Create FormTemplates with JSON configs
                // EnhancedFormBuilderSeeder::class,
                HandwashingSimulationSeeder::class,
                EnsureFormTemplateSeeder::class, // Ensure all ImutProfile have FormTemplate
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
