<?php

namespace App\Traits\DailyReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait DebugTrait
{
    /**
     * Refresh matrix data - called from frontend debug tools
     */
    public function refreshMatrixData(): void
    {
        Log::info('Matrix data refresh requested');
        $this->loadMatrixData();
        $this->dispatch('matrix-refreshed');
    }

    /**
     * Check indicator reports for debugging - called from frontend debug tools
     */
    public function checkIndicatorReports(int $indicatorId, string $date): array
    {
        $realStatus = $this->matrixService->getRealIndicatorStatus($indicatorId, $date);

        // Get matrix data for comparison
        $day = Carbon::parse($date)->day;
        $matrixCell = $this->matrixData[$indicatorId][$day] ?? null;

        $result = [
            'indicator_id' => $indicatorId,
            'date' => $date,
            'day' => $day,
            'database_status' => $realStatus,
            'matrix_data' => $matrixCell,
            'matrix_count' => $matrixCell['count'] ?? 0,
            'matrix_state' => $matrixCell['cell_state'] ?? 'unknown',
            'data_match' => ($realStatus['count'] > 0) === ($matrixCell['has_data'] ?? false)
        ];

        Log::info('Indicator reports check', $result);

        // Send data to frontend for display
        $this->dispatch('indicator-reports-checked', $result);

        return $result;
    }

    /**
     * Get Alpine.js data for frontend rendering
     */
    public function getAlpineData(): array
    {
        return [
            'indicators' => $this->indicators,
            'matrixData' => $this->matrixData,
            'daysInMonth' => $this->daysInMonth,
            'selectedMonth' => $this->selectedMonth,
            'today' => now()->format('Y-m-d'),
        ];
    }
}
