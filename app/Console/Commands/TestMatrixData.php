<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DailyReport\MatrixDataService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestMatrixData extends Command
{
    protected $signature = 'test:matrix-data {--user-id=1} {--month=}';

    protected $description = 'Test matrix data generation for debugging';

    public function handle()
    {
        $userId = $this->option('user-id');
        $month = $this->option('month') ?? now()->format('Y-m');

        // Set authenticated user
        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        Auth::setUser($user);

        $this->info("Testing Matrix Data Generation");
        $this->info("User: {$user->name} (ID: {$user->id})");
        $this->info("Month: {$month}");
        $this->line('');

        // Get user's units
        $unitKerjas = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        $this->info("User's Units: " . implode(', ', $unitKerjas));

        if (empty($unitKerjas)) {
            $this->warn("User has no assigned units");
            return 1;
        }
        $this->line('');

        // Load matrix data
        $service = new MatrixDataService();
        $result = $service->loadMatrixData($month);

        $this->info("=== MATRIX DATA RESULT ===");
        $this->info("Indicators Count: " . count($result['indicators']));
        $this->line('');

        if (count($result['indicators']) > 0) {
            $this->info("Indicators:");
            foreach ($result['indicators'] as $indicator) {
                $this->line("  - [{$indicator['id']}] {$indicator['title']}");
            }
            $this->line('');

            // Check first indicator details
            $firstIndicator = $result['indicators'][0];
            $indicatorId = $firstIndicator['id'];
            $indicatorData = $result['matrixData'][$indicatorId] ?? [];

            $this->info("First Indicator Details:");
            $this->info("  ID: {$indicatorId}");
            $this->info("  Title: {$firstIndicator['title']}");
            $this->info("  Days with data: " . implode(', ', array_keys($indicatorData)));
            $this->line('');

            // Show sample data
            if (!empty($indicatorData)) {
                $firstDay = array_key_first($indicatorData);
                $sampleData = $indicatorData[$firstDay];

                $this->info("Sample Data (Day {$firstDay}):");
                $this->info("  Count: {$sampleData['count']}");
                $this->info("  Cell State: {$sampleData['cell_state']}");
                $this->info("  Compliance %: {$sampleData['compliance_percentage']}");
                $this->line('');

                // Full data structure
                $this->info("Full Sample Data Structure:");
                $this->line(json_encode($sampleData, JSON_PRETTY_PRINT));
            }
        } else {
            $this->warn("No indicators found for this user");
        }

        $this->info("=== DAYS IN MONTH ===");
        $this->line(implode(', ', $result['daysInMonth']));

        return 0;
    }
}
