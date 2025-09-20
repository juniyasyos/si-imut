<?php

namespace App\Services\LaporanImut;

use App\Models\ImutPenilaian;
use App\Strategies\CalculationContext;
use Illuminate\Support\Collection;

class LaporanImutCalculationService
{
    private CalculationContext $calculationContext;

    public function __construct()
    {
        $this->calculationContext = new CalculationContext();
    }

    /**
     * Set calculation strategy based on indicator type or category
     */
    public function setCalculationStrategy(string $type): void
    {
        $this->calculationContext = CalculationContext::createForIndicatorType($type);
    }
    /**
     * Count tercapai indicators
     */
    public function countTercapai(
        Collection $indikatorAktif,
        Collection $penilaianByProfile,
        int $laporanId
    ): int {
        $laporanIndikator = $indikatorAktif->get($laporanId, collect());

        return $laporanIndikator->filter(function ($profile) use ($penilaianByProfile) {
            $profileId = $profile->id;
            $penilaians = $penilaianByProfile->get($profileId, collect());

            return $penilaians->contains(fn($p) => $this->isTercapai($p, $profile));
        })->count();
    }

    /**
     * Count belum dinilai assessments
     */
    public function countBelumDinilai(Collection $penilaians): int
    {
        return $penilaians->filter(function ($p) {
            return is_null($p->numerator_value) && is_null($p->denominator_value);
        })->count();
    }

    /**
     * Count unit that have reported
     */
    public function countUnitMelapor(Collection $penilaians): int
    {
        return $penilaians
            ->filter(fn($p) => !is_null($p->numerator_value) && !is_null($p->denominator_value))
            ->pluck('unit_kerja_id')
            ->unique()
            ->count();
    }

    /**
     * Calculate achievement percentage using strategy pattern
     */
    public function calculateAchievementPercentageWithStrategy(float $numerator, float $denominator, ?string $strategyType = null): float
    {
        if ($strategyType) {
            $this->setCalculationStrategy($strategyType);
        }

        return $this->calculationContext->calculatePercentage($numerator, $denominator);
    }

    /**
     * Calculate achievement percentage (legacy method - maintained for backward compatibility)
     */
    public function calculateAchievementPercentage(float $numerator, float $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    /**
     * Check if indicator is achieved based on baseline
     */
    public function isTercapai($penilaian, $profile): bool
    {
        if (!$penilaian instanceof ImutPenilaian) {
            $penilaian = (object) $penilaian;
        }

        // Check if both numerator and denominator are available
        if (is_null($penilaian->numerator_value) || is_null($penilaian->denominator_value)) {
            return false;
        }

        // Avoid division by zero
        if ($penilaian->denominator_value <= 0) {
            return false;
        }

        // Calculate achievement percentage
        $achievement = $this->calculateAchievementPercentage(
            $penilaian->numerator_value,
            $penilaian->denominator_value
        );

        // Compare with baseline (default 80% if not set)
        $baseline = $profile->benchmarking_baseline ?? 80;

        return $achievement >= $baseline;
    }

    /**
     * Get latest profile IDs from indicators
     */
    public function getLatestProfileIds(Collection $indikatorAktif): Collection
    {
        return $indikatorAktif
            ->flatten()
            ->pluck('id')
            ->unique()
            ->values();
    }

    /**
     * Calculate dashboard statistics
     */
    public function calculateDashboardStats(
        Collection $indikatorAktif,
        Collection $penilaianByProfile,
        Collection $allPenilaian,
        int $laporanId
    ): array {
        $tercapai = $this->countTercapai($indikatorAktif, $penilaianByProfile, $laporanId);

        $unitMelapor = $this->countUnitMelapor($allPenilaian);

        $belumDinilai = $this->countBelumDinilai($allPenilaian);

        $totalIndikator = $indikatorAktif->get($laporanId, collect())->count();

        return [
            'totalIndikator' => $totalIndikator,
            'tercapai' => $tercapai,
            'unitMelapor' => $unitMelapor,
            'belumDinilai' => $belumDinilai,
            'achievementRate' => $totalIndikator > 0 ? round(($tercapai / $totalIndikator) * 100, 2) : 0,
        ];
    }

    /**
     * Process chart data for multiple laporan
     */
    public function processChartData(
        Collection $laporanList,
        Collection $indikatorAktif,
        Collection $penilaianByLaporan,
        Collection $penilaianByProfile
    ): array {
        return $laporanList->map(function ($laporan) use (
            $indikatorAktif,
            $penilaianByLaporan,
            $penilaianByProfile
        ) {
            $laporanId = $laporan->id;
            $penilaian = $penilaianByLaporan->get($laporanId, collect());

            return [
                'tercapai' => $this->countTercapai($indikatorAktif, $penilaianByProfile, $laporanId),
                'unitMelapor' => $penilaian
                    ->filter(fn($p) => !is_null($p->numerator_value) && !is_null($p->denominator_value))
                    ->pluck('unit_kerja_id')
                    ->unique()
                    ->count(),
                'belumDinilai' => $this->countBelumDinilai($penilaian),
            ];
        })->toArray();
    }
}
