<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use App\Support\CacheKey;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RecommendationAnalysisTimMutuWidget extends Widget
{
    protected static string $view = 'filament.widgets.recommendation-analysis-tim-mutu-widget';

    // Cache duration in seconds (30 minutes)
    private const CACHE_DURATION = 1800;

    public static function canView(): bool
    {
        try {
            $user = Auth::user();

            if (!$user) {
                \Log::debug('RecommendationAnalysisTimMutuWidget: No authenticated user');
                return false;
            }

            $hasRole = $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);
            \Log::debug('RecommendationAnalysisTimMutuWidget::canView', [
                'user_id' => $user->id,
                'has_tim_mutu_role' => $hasRole,
            ]);

            return $hasRole;
        } catch (\Exception $e) {
            \Log::error('Error in RecommendationAnalysisTimMutuWidget::canView', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public array $expandedDetails = []; // Track laporan mana yang detail unit kerja-nya di-expand

    /**
     * Get semua laporan yang sedang dalam fase pengisian analisis dan rekomendasi
     * Menggunakan caching dengan eager loading untuk mencegah N+1 queries
     */
    public function getOngoingAnalysisReports(): array
    {
        return Cache::remember(
            CacheKey::recommendationAnalysisTimMutuOngoing(),
            self::CACHE_DURATION,
            fn() => $this->computeOngoingAnalysisReports()
        );
    }

    /**
     * Komputasi ongoing analysis reports dengan eager loading
     */
    private function computeOngoingAnalysisReports(): array
    {
        $today = Carbon::today();

        // Eager load relationships untuk mencegah N+1 queries
        $reports = LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
            ->whereDate('assessment_period_end', '<', $today)
            ->with('unitKerjas', 'laporanUnitKerjas.imutPenilaians', 'laporanUnitKerjas.unitKerja') // Eager load semua relasi
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

                    // Get overall completion stats (dari loaded relationships)
                    $stats = $this->getOverallCompletionStats($laporan);

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
     * Get overall analysis completion statistics for a laporan
     * Shows completion across ALL unit kerjas
     * Menggunakan eager-loaded relationships untuk menghindari query tambahan
     * @return array ['total_units' => int, 'completed_units' => int, 'percentage' => int, 'unit_details' => array]
     */
    public function getOverallCompletionStats(LaporanImut $laporan): array
    {
        return Cache::remember(
            CacheKey::recommendationAnalysisCompletionStats($laporan->id),
            self::CACHE_DURATION,
            fn() => $this->computeOverallCompletionStats($laporan)
        );
    }

    /**
     * Komputasi completion stats dari loaded relationships
     */
    private function computeOverallCompletionStats(LaporanImut $laporan): array
    {
        // Load if not already loaded (fail-safe)
        if (!$laporan->relationLoaded('laporanUnitKerjas')) {
            $laporan->load('laporanUnitKerjas.imutPenilaians', 'laporanUnitKerjas.unitKerja');
        }

        $laporanUnitKerjas = $laporan->laporanUnitKerjas;
        $totalUnits = $laporanUnitKerjas->count();

        if ($totalUnits === 0) {
            return [
                'total_units' => 0,
                'completed_units' => 0,
                'percentage' => 0,
                'pending_units' => 0,
                'unit_details' => [],
            ];
        }

        $unitDetails = [];
        $completedCount = 0;

        // Perhitungan deadline
        $periodEnd = $laporan->assessment_period_end;
        $analysisDuration = $laporan->recommendation_analysis_duration ?? 0;
        $analysisDeadline = $periodEnd->copy()->addDays($analysisDuration);
        $today = Carbon::today();

        // Menggunakan eager-loaded laporanUnitKerjas dan imutPenilaians
        foreach ($laporanUnitKerjas as $laporanUnitKerja) {
            $unitKerja = $laporanUnitKerja->unitKerja; // Lazy load jika diperlukan
            $imutPenilaians = $laporanUnitKerja->imutPenilaians;

            $totalPenilaians = $imutPenilaians->count();
            $completedPenilaians = $imutPenilaians->filter(function ($penilaian) {
                return !is_null($penilaian->analysis) || !is_null($penilaian->recommendations);
            })->count();

            $percentage = $totalPenilaians > 0 ? round(($completedPenilaians / $totalPenilaians) * 100) : 0;
            $isCompleted = $totalPenilaians > 0 && $percentage === 100;
            $daysRemaining = $today->diffInDays($analysisDeadline, false);
            $isOverdue = $today->gt($analysisDeadline);

            if ($isCompleted) {
                $completedCount++;
            }

            // Status text untuk UX yang lebih jelas
            $statusText = match (true) {
                $isCompleted => 'Selesai',
                $isOverdue => 'Melewati Deadline',
                $daysRemaining < 1 => 'URGENT - Hari Terakhir',
                $daysRemaining <= 2 => 'Mendekati Deadline',
                default => ($totalPenilaians === 0 ? 'Tidak Ada Data' : 'Dalam Proses')
            };

            $unitDetails[] = [
                'unit_kerja_id' => $laporanUnitKerja->unit_kerja_id,
                'unit_name' => $unitKerja->unit_name ?? 'Unknown',
                'total' => $totalPenilaians,
                'completed' => $completedPenilaians,
                'percentage' => $percentage,
                'is_completed' => $isCompleted,
                'analysis_deadline' => $analysisDeadline,
                'period_end' => $periodEnd,
                'days_remaining' => $daysRemaining,
                'is_overdue' => $isOverdue,
                'status_text' => $statusText,
            ];
        }

        $pendingUnits = $totalUnits - $completedCount;
        $percentage = $totalUnits > 0 ? round(($completedCount / $totalUnits) * 100) : 0;

        return [
            'total_units' => $totalUnits,
            'completed_units' => $completedCount,
            'pending_units' => $pendingUnits,
            'percentage' => $percentage,
            'unit_details' => $unitDetails,
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

    /**
     * Get laporan sebelumnya (last completed/finished report) jika tidak ada laporan yang sedang berjalan
     * Menggunakan caching untuk menghindari query berulang
     */
    public function getPreviousAnalysisReport(): ?array
    {
        return Cache::remember(
            CacheKey::recommendationAnalysisTimMutuPrevious(),
            self::CACHE_DURATION,
            fn() => $this->computePreviousAnalysisReport()
        );
    }

    /**
     * Komputasi previous analysis report dengan eager loading
     */
    private function computePreviousAnalysisReport(): ?array
    {
        // Cari laporan yang sudah selesai/tidak sedang dalam analisis
        // Eager load relationships untuk mencegah N+1
        $previousReport = LaporanImut::whereDate('assessment_period_end', '<', Carbon::today())
            ->where(function ($query) {
                // Status bukan process, atau sudah lewat deadline analisis
                $query->where('status', '!=', LaporanImut::STATUS_PROCESS)
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', LaporanImut::STATUS_PROCESS)
                            ->whereRaw('DATE_ADD(assessment_period_end, INTERVAL recommendation_analysis_duration DAY) < CURDATE()');
                    });
            })
            ->with('unitKerjas', 'laporanUnitKerjas.imutPenilaians', 'laporanUnitKerjas.unitKerja') // Eager load
            ->orderBy('assessment_period_end', 'desc')
            ->first();

        if (!$previousReport) {
            return null;
        }

        $stats = $this->computeOverallCompletionStats($previousReport);
        
        $periodEnd = $previousReport->assessment_period_end;
        $analysisDuration = $previousReport->recommendation_analysis_duration ?? 0;
        $analysisDeadline = $periodEnd->copy()->addDays($analysisDuration);

        return [
            'id' => $previousReport->id,
            'name' => $previousReport->name,
            'slug' => $previousReport->slug,
            'period_end' => $previousReport->assessment_period_end,
            'analysis_deadline' => $analysisDeadline,
            'laporan' => $previousReport,
            'completion_stats' => $stats,
            'is_previous' => true,
        ];
    }

    /**
     * Toggle expanded state untuk detail unit kerja
     */
    public function toggleExpandedDetails(int $laporanId): void
    {
        if (in_array($laporanId, $this->expandedDetails)) {
            $this->expandedDetails = array_filter($this->expandedDetails, fn($id) => $id !== $laporanId);
        } else {
            $this->expandedDetails[] = $laporanId;
        }
    }

    /**
     * Check apakah detail laporan sedang di-expand
     */
    public function isDetailExpanded(int $laporanId): bool
    {
        return in_array($laporanId, $this->expandedDetails);
    }
}
