<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ImutDataSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                KaidoSettingSeeder::class,
                ShieldSeeder::class,
                UserSeeder::class,
                UnitKerjaSeeder::class,
                RoleUpgradeSeeder::class,
                ImutCategorySeeder::class,
                RegionTypeSeeder::class,
                ImutDataOldSeeder::class,
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
