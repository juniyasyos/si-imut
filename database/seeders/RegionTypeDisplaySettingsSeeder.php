<?php

namespace Database\Seeders;

use App\Models\RegionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionTypeDisplaySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder ini mengisi default display settings untuk region types yang sudah ada.
     */
    public function run(): void
    {
        $this->command->info('🎨 Updating display settings for existing region types...');

        // Update Nasional
        $updated = RegionType::where('type', 'LIKE', '%Nasional%')
            ->orWhere('type', 'LIKE', '%National%')
            ->update([
                'display_color' => '#10b981', // Green
                'chart_type' => 'column',
            ]);

        if ($updated > 0) {
            $this->command->info("✅ Updated {$updated} Nasional region type(s)");
        }

        // Update Provinsi
        $updated = RegionType::where('type', 'LIKE', '%Provinsi%')
            ->orWhere('type', 'LIKE', '%Province%')
            ->update([
                'display_color' => '#8b5cf6', // Purple
                'chart_type' => 'column',
            ]);

        if ($updated > 0) {
            $this->command->info("✅ Updated {$updated} Provinsi region type(s)");
        }

        // Update Rumah Sakit
        $updated = RegionType::where('type', 'LIKE', '%Rumah Sakit%')
            ->orWhere('type', 'LIKE', '%Hospital%')
            ->orWhere('type', 'LIKE', '%RS%')
            ->update([
                'display_color' => '#ef4444', // Red
                'chart_type' => 'line',
            ]);

        if ($updated > 0) {
            $this->command->info("✅ Updated {$updated} Rumah Sakit region type(s)");
        }

        // Update yang belum terisi dengan default
        $updated = RegionType::whereNull('display_color')
            ->orWhereNull('chart_type')
            ->update([
                'display_color' => '#3b82f6', // Blue
                'chart_type' => 'column',
            ]);

        if ($updated > 0) {
            $this->command->info("✅ Updated {$updated} other region type(s) with default settings");
        }

        $this->command->info('🎉 Display settings update completed!');
    }
}
