<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugBladeRendering extends Command
{
    protected $signature = 'debug:blade-rendering {--user-id=2}';

    protected $description = 'Test blade rendering with Livewire data';

    public function handle()
    {
        $userId = $this->option('user-id');

        $user = \App\Models\User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        // Simulate Livewire state
        \Illuminate\Support\Facades\Auth::setUser($user);

        // Simulate what Livewire component has after mount()
        $component = new \App\Filament\Resources\DailyReportEntryResource\Pages\ListDailyReportEntries();

        // Call mount to populate properties
        $component->bootBase();
        $component->loadMatrixData();

        $this->info("=== Livewire Component State After Mount ===");
        $this->line("User: {$user->name}");
        $this->line('');

        // Check properties
        $this->info("1. Indicators Property:");
        $this->line("   Type: " . gettype($component->indicators));
        $this->line("   Count: " . count($component->indicators ?? []));

        if (!empty($component->indicators)) {
            $sample = $component->indicators[0];
            $this->line("   Sample: " . json_encode($sample));
        }
        $this->line('');

        $this->info("2. Matrix Data Property:");
        $this->line("   Type: " . gettype($component->matrixData));
        $this->line("   Count: " . count($component->matrixData ?? []));

        if (!empty($component->matrixData)) {
            $firstKey = array_key_first($component->matrixData);
            $this->line("   First Indicator ID: {$firstKey}");
            $this->line("   Days available: " . count($component->matrixData[$firstKey] ?? []));

            // Check if can be encoded
            try {
                $encoded = json_encode($component->matrixData[$firstKey][1]);
                $this->line("   Sample day 1 JSON: " . substr($encoded, 0, 100) . "...");
            } catch (\Exception $e) {
                $this->line("   ERROR encoding: " . $e->getMessage());
            }
        }
        $this->line('');

        $this->info("3. Checking @js() Compatibility:");

        // Test if data can be safely encoded by Blade @js()
        try {
            // This simulates what @js() does
            $jsData = json_encode($component->matrixData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APO | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
            $this->line("   ✓ MatrixData can be encoded to JavaScript");
            $this->line("   Size: " . strlen($jsData) . " bytes");
        } catch (\Exception $e) {
            $this->error("   ✗ Error encoding: " . $e->getMessage());
        }

        // Try to check Blade rendering
        $this->line('');
        $this->info("4. Testing Blade @js() Directive:");

        try {
            $blade = \Illuminate\Support\Facades\View::make('test-js-data', [
                'matrixData' => $component->matrixData
            ]);
            $this->line("   ✓ Can render template with data");
        } catch (\Exception $e) {
            // Template doesn't exist, try inline
            try {
                $content = "@js(\$matrixData)";
                $this->line("   Testing inline @js() directive...");
            } catch (\Exception $e) {
                $this->line("   Could not test inline");
            }
        }

        return 0;
    }
}
