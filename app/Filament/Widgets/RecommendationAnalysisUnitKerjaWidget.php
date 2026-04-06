<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Support\CacheKey;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RecommendationAnalysisUnitKerjaWidget extends Widget
{
    protected static string $view = 'filament.widgets.recommendation-analysis-unit-kerja-widget';

    // Cache duration in seconds (30 minutes)
    private const CACHE_DURATION = 1800;

    public static function canView(): bool
    {
        try {
            $user = Auth::user();

            if (!$user) {
                \Log::debug('RecommendationAnalysisUnitKerjaWidget: No authenticated user');
                return false;
            }

            $hasUnitKerja = $user->unitKerjas()->exists();
            $isAdminOrTimMutu = $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu']);

            $canView = $hasUnitKerja && !$isAdminOrTimMutu;

            \Log::debug('RecommendationAnalysisUnitKerjaWidget::canView', [
                'user_id' => $user->id,
                'has_unit_kerja' => $hasUnitKerja,
                'is_admin_or_tim_mutu' => $isAdminOrTimMutu,
                'can_view' => $canView,
            ]);

            return $canView;
        } catch (\Exception $e) {
            \Log::error('Error in RecommendationAnalysisUnitKerjaWidget::canView', [
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
     * Get laporan-laporan yang relevan untuk unit kerja user
     * (laporan yang sedang dalam fase analisis dan user punya akses)
     * Menggunakan caching dengan eager loading untuk mencegah N+1 queries
     */
    public function getRelevantAnalysisReports(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        // Cache per user untuk memastikan setiap user punya cachanya sendiri
        return Cache::remember(
            CacheKey::recommendationAnalysisUnitKerjaOngoing($user->id),
            self::CACHE_DURATION,
            fn() => $this->computeRelevantAnalysisReports()
        );
    }

    /**
     * Komputasi relevant analysis reports dengan eager loading
     */
    private function computeRelevantAnalysisReports(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        // Eager load user's unit kerjas untuk menghindari N+1
        $userUnitKerjaIds = $user->unitKerjas()->pluck('id')->toArray();

        if (empty($userUnitKerjaIds)) {
            return [];
        }

        $today = Carbon::today();

        // Eager load semua relasi yang diperlukan
        $reports = LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
            ->whereDate('assessment_period_end', '<', $today)
            ->whereHas('unitKerjas', function ($query) use ($userUnitKerjaIds) {
                $query->whereIn('unit_kerja.id', $userUnitKerjaIds);
            })
            ->with('unitKerjas', 'laporanUnitKerjas.imutPenilaians', 'laporanUnitKerjas.unitKerja') // Eager load
            ->get()
            ->map(function (LaporanImut $laporan) use ($today, $userUnitKerjaIds) {
                $periodEnd = $laporan->assessment_period_end;
                $analysisDuration = $laporan->recommendation_analysis_duration ?? 0;
                $analysisDeadline = $periodEnd->copy()->addDays($analysisDuration);

                // Cek apakah masih dalam phase analisis
                if ($today->lte($analysisDeadline)) {
                    $daysRemaining = $today->diffInDays($analysisDeadline, false);
                    $status = match (true) {
                        $daysRemaining < 1 => 'urgent',
                        $daysRemaining <= 2 => 'warning',
                        default => 'info'
                    };

                    // Get user's unit kerja completion status (dari loaded relationships)
                    $userStats = $this->computeUserUnitKerjaCompletionStats($laporan, $userUnitKerjaIds);

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
                        'user_completion_stats' => $userStats,
                    ];
                }

                return null;
            })
            ->filter()
            ->sortBy(function ($item) {
                return $item['days_remaining'];
            })
            ->values()
            ->all();

        return $reports;
    }

    /**
     * Get completion status untuk unit kerja user saja
     * Menggunakan eager-loaded relationships untuk menghindari query tambahan
     * @return array status pengisian analisis untuk user's units
     */
    public function getUserUnitKerjaCompletionStats(LaporanImut $laporan, array $userUnitKerjaIds): array
    {
        // Untuk kompatibilitas dengan existing code
        return $this->computeUserUnitKerjaCompletionStats($laporan, $userUnitKerjaIds);
    }

    /**
     * Komputasi completion stats dari loaded relationships
     */
    private function computeUserUnitKerjaCompletionStats(LaporanImut $laporan, array $userUnitKerjaIds): array
    {
        // Load if not already loaded (fail-safe)
        if (!$laporan->relationLoaded('laporanUnitKerjas')) {
            $laporan->load('laporanUnitKerjas.imutPenilaians', 'laporanUnitKerjas.unitKerja');
        }

        $stats = [];

        // Perhitungan deadline
        $periodEnd = $laporan->assessment_period_end;
        $analysisDuration = $laporan->recommendation_analysis_duration ?? 0;
        $analysisDeadline = $periodEnd->copy()->addDays($analysisDuration);
        $today = Carbon::today();

        // Menggunakan eager-loaded laporanUnitKerjas
        $userLaporanUnitKerjas = $laporan->laporanUnitKerjas
            ->whereIn('unit_kerja_id', $userUnitKerjaIds)
            ->values();

        foreach ($userLaporanUnitKerjas as $laporanUnitKerja) {
            $unitKerja = UnitKerja::find($laporanUnitKerja->unit_kerja_id);
            $imutPenilaians = $laporanUnitKerja->imutPenilaians;

            $totalPenilaians = $imutPenilaians->count();
            $completedPenilaians = $imutPenilaians->filter(function ($penilaian) {
                return !is_null($penilaian->analysis) || !is_null($penilaian->recommendations);
            })->count();

            $percentage = $totalPenilaians > 0 ? round(($completedPenilaians / $totalPenilaians) * 100) : 0;
            $isCompleted = $totalPenilaians > 0 && $percentage === 100;
            $daysRemaining = $today->diffInDays($analysisDeadline, false);
            $isOverdue = $today->gt($analysisDeadline);

            // Status text untuk UX yang lebih jelas
            $statusText = match (true) {
                $isCompleted => 'Selesai',
                $isOverdue => 'Melewati Deadline',
                $daysRemaining < 1 => 'URGENT - Hari Terakhir',
                $daysRemaining <= 2 => 'Mendekati Deadline',
                default => ($totalPenilaians === 0 ? 'Tidak Ada Data' : 'Dalam Proses')
            };

            $stats[] = [
                'unit_kerja_id' => $laporanUnitKerja->unit_kerja_id,
                'unit_name' => $unitKerja?->unit_name ?? 'Unknown',
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

        return $stats;
    }

    /**
     * Get count of relevant reports
     */
    public function getRelevantAnalysisCount(): int
    {
        return count($this->getRelevantAnalysisReports());
    }

    /**
     * Get most urgent report
     */
    public function getMostUrgentReport(): ?array
    {
        $reports = $this->getRelevantAnalysisReports();
        return $reports[0] ?? null;
    }

    /**
     * Get laporan sebelumnya (last completed/finished report) untuk unit kerja user
     * Jika tidak ada laporan yang sedang berjalan
     * Menggunakan caching untuk menghindari query berulang
     */
    public function getPreviousRelevantAnalysisReport(): ?array
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // Cache per user
        return Cache::remember(
            CacheKey::recommendationAnalysisUnitKerjaPrevious($user->id),
            self::CACHE_DURATION,
            fn() => $this->computePreviousRelevantAnalysisReport()
        );
    }

    /**
     * Komputasi previous relevant analysis report dengan eager loading
     */
    private function computePreviousRelevantAnalysisReport(): ?array
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $userUnitKerjaIds = $user->unitKerjas()->pluck('id')->toArray();

        if (empty($userUnitKerjaIds)) {
            return null;
        }

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
            ->whereHas('unitKerjas', function ($query) use ($userUnitKerjaIds) {
                $query->whereIn('unit_kerja.id', $userUnitKerjaIds);
            })
            ->with('unitKerjas', 'laporanUnitKerjas.imutPenilaians', 'laporanUnitKerjas.unitKerja') // Eager load
            ->orderBy('assessment_period_end', 'desc')
            ->first();

        if (!$previousReport) {
            return null;
        }

        $userStats = $this->computeUserUnitKerjaCompletionStats($previousReport, $userUnitKerjaIds);

        return [
            'id' => $previousReport->id,
            'name' => $previousReport->name,
            'slug' => $previousReport->slug,
            'period_end' => $previousReport->assessment_period_end,
            'laporan' => $previousReport,
            'user_completion_stats' => $userStats,
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
