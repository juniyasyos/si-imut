<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DailyReport\MatrixDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class DebugMatrixResponse extends Command
{
    protected $signature = 'debug:matrix-response {--user-id=2} {--month=2026-04}';

    protected $description = 'Debug what MatrixDataService sends to Livewire component';

    public function handle()
    {
        $userId = $this->option('user-id');
        $month = $this->option('month');

        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        Auth::setUser($user);

        $this->info("=== Matrix Data Response Debug ===");
        $this->line("User: {$user->name} (ID: {$userId})");
        $this->line("Month: {$month}");
        $this->line('');

        // Load matrix data exactly as Livewire would
        $service = new MatrixDataService();
        $result = $service->loadMatrixData($month);

        // Check what's actually being sent
        $this->info("Data Structure Sent to View:");
        $this->line('');

        $this->info("1. Indicators Count: " . count($result['indicators']));
        $this->line("Sample Indicator:");
        if (count($result['indicators']) > 0) {
            $sample = $result['indicators'][0];
            $this->line(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $this->line('');

        $this->info("2. Matrix Data Structure:");
        $this->line("Total indicator keys: " . count($result['matrixData']));

        if (count($result['matrixData']) > 0) {
            $firstIndicatorId = array_key_first($result['matrixData']);
            $firstIndicatorData = $result['matrixData'][$firstIndicatorId];

            $this->line("First Indicator ID: {$firstIndicatorId}");
            $this->line("Days available: " . count($firstIndicatorData));

            if (count($firstIndicatorData) > 0) {
                $firstDay = array_key_first($firstIndicatorData);
                $this->line("Sample Day {$firstDay}:");
                $this->line(json_encode($firstIndicatorData[$firstDay], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
        $this->line('');

        $this->info("3. Days in Month: " . count($result['daysInMonth']));
        $this->line("Days: " . implode(', ', $result['daysInMonth']));
        $this->line('');

        // Check if any data has count > 0
        $this->info("4. Data Summary:");
        $totalCells = 0;
        $cellsWithData = 0;
        $totalReports = 0;

        foreach ($result['matrixData'] as $indicatorId => $days) {
            foreach ($days as $day => $cell) {
                $totalCells++;
                if ($cell['count'] > 0) {
                    $cellsWithData++;
                    $totalReports += $cell['count'];
                }
            }
        }

        $this->line("Total cells: {$totalCells}");
        $this->line("Cells with data: {$cellsWithData}");
        $this->line("Total reports: {$totalReports}");
        $this->line('');

        // Show sample cells with data
        if ($cellsWithData > 0) {
            $this->info("5. Sample Cells with Data:");
            $count = 0;
            foreach ($result['matrixData'] as $indicatorId => $days) {
                foreach ($days as $day => $cell) {
                    if ($cell['count'] > 0 && $count < 5) {
                        $indicator = collect($result['indicators'])->firstWhere('id', $indicatorId);
                        $this->line("  [{$indicatorId}] {$indicator['title']} - Day {$day}: {$cell['count']} reports");
                        $count++;
                    }
                }
            }
        }

        return 0;
    }
}
