<?php

namespace App\Services\Filament\Widgets;

use App\Models\LaporanImut;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanWidgetService
{
    /**
     * Check if user can view laporan widget
     */
    public function canViewLaporan(): bool
    {
        $user = Auth::user();
        return $user && $user->can('widget_LaporanLatestWidget');
    }

    /**
     * Get latest active laporan
     */
    public function getLatestLaporan(): ?LaporanImut
    {
        $today = Carbon::today();

        // Try to get currently active laporan
        $activeLaporan = LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
            ->whereDate('assessment_period_start', '<=', $today)
            ->whereDate('assessment_period_end', '>=', $today)
            ->orderByDesc('assessment_period_start')
            ->first();

        // If no active laporan, get latest completed
        if (!$activeLaporan) {
            $activeLaporan = LaporanImut::latest('assessment_period_start')
                ->where('status', LaporanImut::STATUS_COMPLETE)
                ->first();
        }

        return $activeLaporan;
    }

    /**
     * Get laporan widget data
     */
    public function getLaporanWidgetData(): array
    {
        $laporan = $this->getLatestLaporan();

        if (!$laporan) {
            return [
                'laporan' => null,
                'status' => 'no_data',
                'message' => 'Belum ada laporan tersedia'
            ];
        }

        return [
            'laporan' => $laporan,
            'status' => $this->getLaporanStatus($laporan),
            'period' => $this->formatLaporanPeriod($laporan),
            'progress' => $this->calculateProgress($laporan)
        ];
    }

    /**
     * Get laporan status for display
     */
    private function getLaporanStatus(LaporanImut $laporan): string
    {
        $today = Carbon::today();

        if ($laporan->status === LaporanImut::STATUS_PROCESS) {
            if ($today->between($laporan->assessment_period_start, $laporan->assessment_period_end)) {
                return 'active';
            }
            return 'process';
        }

        return 'completed';
    }

    /**
     * Format laporan period for display
     */
    private function formatLaporanPeriod(LaporanImut $laporan): string
    {
        $start = Carbon::parse($laporan->assessment_period_start)->format('d M Y');
        $end = Carbon::parse($laporan->assessment_period_end)->format('d M Y');

        return "{$start} - {$end}";
    }

    /**
     * Calculate laporan progress percentage
     */
    private function calculateProgress(LaporanImut $laporan): int
    {
        $today = Carbon::today();
        $start = Carbon::parse($laporan->assessment_period_start);
        $end = Carbon::parse($laporan->assessment_period_end);

        if ($today->lt($start)) {
            return 0;
        }

        if ($today->gt($end)) {
            return 100;
        }

        $totalDays = $start->diffInDays($end);
        $passedDays = $start->diffInDays($today);

        return $totalDays > 0 ? round(($passedDays / $totalDays) * 100) : 0;
    }
}
