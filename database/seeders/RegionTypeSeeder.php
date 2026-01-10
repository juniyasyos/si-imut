<?php

namespace Database\Seeders;

use App\Models\RegionType;
use App\Models\ImutData;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RegionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan membuat default region types untuk setiap ImutData yang ada.
     * Region types yang dibuat: Nasional, Provinsi, Rumah Sakit
     */
    public function run(): void
    {
        // Default region type yang akan dibuat untuk SEMUA ImutData
        $defaultNationalType = [
            'type' => '🌐 Nasional',
            'display_color' => '#10b981',
            'chart_type' => 'column',
        ];

        // Region types tambahan yang hanya untuk ImutData tertentu
        $additionalTypes = [
            [
                'type' => '🏛️ Provinsi',
                'display_color' => '#8b5cf6',
                'chart_type' => 'column',
            ],
            [
                'type' => '🏥 Rumah Sakit',
                'display_color' => '#ef4444',
                'chart_type' => 'line',
            ],
        ];

        // Daftar ImutData yang mendapat region type lengkap
        $imutDataWithFullRegions = [
            'Kepatuhan Kebersihan Tangan',
            // Tambahkan nama ImutData lain yang perlu region type lengkap di sini
        ];

        // Ambil semua ImutData yang ada
        $imutDataList = ImutData::all();

        $this->command->info("Creating region types for {$imutDataList->count()} ImutData records...");

        foreach ($imutDataList as $imutData) {
            $this->command->info("Processing ImutData: {$imutData->title}");

            // Buat RegionType Nasional untuk semua ImutData
            $nationalData = array_merge($defaultNationalType, ['imut_data_id' => $imutData->id]);
            RegionType::firstOrCreate(
                [
                    'imut_data_id' => $imutData->id,
                    'type' => $defaultNationalType['type'],
                ],
                $nationalData
            );

            // Jika ImutData ini termasuk yang mendapat region type lengkap, tambahkan region type lainnya
            if (in_array($imutData->title, $imutDataWithFullRegions)) {
                $this->command->info("  → Creating additional region types for: {$imutData->title}");

                foreach ($additionalTypes as $typeData) {
                    $data = array_merge($typeData, ['imut_data_id' => $imutData->id]);

                    RegionType::firstOrCreate(
                        [
                            'imut_data_id' => $imutData->id,
                            'type' => $typeData['type'],
                        ],
                        $data
                    );
                }
            }
        }

        $this->command->info('✅ Region types created successfully:');
        $this->command->info('   • All ImutData: Nasional region type');
        $this->command->info('   • Selected ImutData: Additional Provinsi & Rumah Sakit region types');
    }
}
