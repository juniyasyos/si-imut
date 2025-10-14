<?php

namespace Database\Seeders;

use App\Settings\KaidoSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaidoSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if settings already exist
        $existingSettings = DB::table('settings')
            ->where('group', 'KaidoSetting')
            ->pluck('name')
            ->toArray();

        $defaultSettings = [
            'site_name' => 'SIIMUT',
            'site_active' => true,
            'registration_enabled' => false,
            'login_enabled' => true,
            'password_reset_enabled' => true,
            'sso_enabled' => false,
        ];

        foreach ($defaultSettings as $key => $value) {
            $settingName = "KaidoSetting.{$key}";

            // Only insert if setting doesn't exist
            if (!in_array($settingName, $existingSettings)) {
                DB::table('settings')->insert([
                    'group' => 'KaidoSetting',
                    'name' => $settingName,
                    'locked' => false,
                    'payload' => json_encode($value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('KaidoSetting default values have been seeded.');
    }
}
