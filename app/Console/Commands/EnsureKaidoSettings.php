<?php

namespace App\Console\Commands;

use Exception;
use App\Settings\KaidoSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnsureKaidoSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:ensure-kaido';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all required KaidoSetting properties exist in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking KaidoSetting properties...');

        $defaultSettings = [
            'site_name' => 'SIIMUT',
            'site_active' => true,
            'registration_enabled' => false,
            'login_enabled' => true,
            'password_reset_enabled' => true,
            'sso_enabled' => false,
        ];

        $existingSettings = DB::table('settings')
            ->where('group', 'KaidoSetting')
            ->pluck('payload', 'name')
            ->toArray();

        $created = 0;
        $updated = 0;

        foreach ($defaultSettings as $key => $value) {
            $settingName = "KaidoSetting.{$key}";

            if (!array_key_exists($settingName, $existingSettings)) {
                // Create missing setting
                DB::table('settings')->insert([
                    'group' => 'KaidoSetting',
                    'name' => $settingName,
                    'locked' => false,
                    'payload' => json_encode($value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
                $this->line("✓ Created setting: {$key} = " . json_encode($value));
            } else {
                $this->line("✓ Setting exists: {$key}");
            }
        }

        if ($created > 0) {
            $this->info("Created {$created} missing settings.");
        } else {
            $this->info("All KaidoSetting properties are present.");
        }

        // Test if KaidoSetting can be loaded
        try {
            $settings = app(KaidoSetting::class);
            $this->info("✓ KaidoSetting successfully loaded.");
            $this->line("   - site_name: {$settings->site_name}");
            $this->line("   - site_active: " . ($settings->site_active ? 'true' : 'false'));
            $this->line("   - registration_enabled: " . ($settings->registration_enabled ? 'true' : 'false'));
            $this->line("   - login_enabled: " . ($settings->login_enabled ? 'true' : 'false'));
            $this->line("   - password_reset_enabled: " . ($settings->password_reset_enabled ? 'true' : 'false'));
            $this->line("   - sso_enabled: " . ($settings->sso_enabled ? 'true' : 'false'));
        } catch (Exception $e) {
            $this->error("✗ Failed to load KaidoSetting: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
