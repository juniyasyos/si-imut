<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RecommendationAnalysisRunningWidget extends Widget
{
    protected static string $view = 'filament.widgets.recommendation-analysis-running-widget';

    public static function canView(): bool
    {
        return Auth::user()?->can('view_any', LaporanImut::class);
    }

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    /**
     * Get laporan-laporan yang sedang dalam fase pengisian analisis dan rekomendasi
     */
    public function getOngoingAnalysisReports(): array
    {
        $today = Carbon::today();
        
        $reports = LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
            ->whereDate('assessment_period_end', '<', $today)
            ->get()
            ->map(function (LaporanImut $laporan) use ($today) {
                $periodEnd = $laporan->assessment_period_end;
                $analysisDuration = $laporan->recommendation_analysis_duration ?? 0;
                $analysisDeadline = $periodEnd->copy()->addDays($analysisDuration);
                
                // Cek apakah masih dalam phase analisis
                if ($today->lte($analysisDeadline)) {
                    $daysRemaining = $today->diffInDays($analysisDeadline, false);
                    $status = match (true) {
                        $daysRemaining < 1 => 'urgent', // Kurang dari 1 hari
                        $daysRemaining <= 2 => 'warning', // 1-2 hari
                        default => 'info'
                    };
                    
                    // Get analysis completion stats
                    $stats = $this->getAnalysisCompletionStats($laporan);
                    
                    return [
                        'id' => $laporan->id,
                        'name' => $laporan->name,
                        'slug' => $laporan->slug,
                        'period_end' => $periodEnd,
                        'analysis_deadline' => $analysisDeadline,
                        'days_remaining' => $daysRemaining,
                        'status' => $status,
                        'is_overdue' => $today->gt($analysisDeadline),
                        'laporan' => $laporan,
                        'completion_stats' => $stats,
                    ];
                }
                
                return null;
            })
            ->filter()
            ->sortBy(function ($item) {
                // Sort by days remaining (deadline yang paling dekat duluan)
                return $item['days_remaining'];
            })
            ->values()
            ->all();
            
        return $reports;
    }

    /**
     * Get analysis completion statistics for a laporan
     * @return array ['total_units' => int, 'completed_units' => int, 'percentage' => int]
     */
    public function getAnalysisCompletionStats(LaporanImut $laporan): array
    {
        $totalUnits = $laporan->unitKerjas()->count();
        
        if ($totalUnits === 0) {
            return [
                'total_units' => 0,
                'completed_units' => 0,
                'percentage' => 0,
                'pending_units' => 0,
            ];
        }
        
        // Count units yang sudah selesai mengisi analisis
        // Asumsi: unit dianggap selesai jika semua imut_penilaians sudah memiliki analysis/recommendation
        $completedUnits = $laporan->laporanUnitKerjas()
            ->whereHas('imutPenilaians', function ($query) {
                // Check if all penilaians have completed analysis
                $query->whereNotNull('analisis')
                    ->orWhereNotNull('rekomendasi');
            })
            ->distinct('unit_kerja_id')
            ->count();
        
        $pendingUnits = $totalUnits - $completedUnits;
        $percentage = $totalUnits > 0 ? round(($completedUnits / $totalUnits) * 100) : 0;
        
        return [
            'total_units' => $totalUnits,
            'completed_units' => $completedUnits,
            'pending_units' => $pendingUnits,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get count of ongoing analysis reports
     */
    public function getOngoingAnalysisCount(): int
    {
        return count($this->getOngoingAnalysisReports());
    }

    /**
     * Get the most urgent report (fewest days remaining)
     */
    public function getMostUrgentReport(): ?array
    {
        $reports = $this->getOngoingAnalysisReports();
        return $reports[0] ?? null;
    }
}
