<?php

use App\Services\LaporanImut\LaporanImutCalculationService;
use App\Models\ImutPenilaian;

beforeEach(function () {
    $this->service = app(LaporanImutCalculationService::class);
});

describe('LaporanImutCalculationService', function () {
    it('can calculate achievement percentage', function () {
        $percentage = $this->service->calculateAchievementPercentage(80, 100);

        expect($percentage)->toBe(80.0);
    });

    it('returns zero for invalid denominator', function () {
        $percentage = $this->service->calculateAchievementPercentage(50, 0);

        expect($percentage)->toBe(0.0);
    });

    it('rounds percentage to 2 decimal places', function () {
        $percentage = $this->service->calculateAchievementPercentage(1, 3);

        expect($percentage)->toBe(33.33);
    });

    it('can check if indicator is achieved', function () {
        // Create mock penilaian object
        $penilaian = (object) [
            'numerator_value' => 85,
            'denominator_value' => 100,
        ];

        // Create mock profile with baseline
        $profile = (object) [
            'benchmarking_baseline' => 80,
        ];

        $result = $this->service->isTercapai($penilaian, $profile);

        expect($result)->toBe(true);
    });

    it('returns false for indicator below baseline', function () {
        $penilaian = (object) [
            'numerator_value' => 75,
            'denominator_value' => 100,
        ];

        $profile = (object) [
            'benchmarking_baseline' => 80,
        ];

        $result = $this->service->isTercapai($penilaian, $profile);

        expect($result)->toBe(false);
    });

    it('uses default baseline of 80 when not set', function () {
        $penilaian = (object) [
            'numerator_value' => 85,
            'denominator_value' => 100,
        ];

        $profile = (object) []; // No baseline set

        $result = $this->service->isTercapai($penilaian, $profile);

        expect($result)->toBe(true);
    });

    it('returns false for null values', function () {
        $penilaian = (object) [
            'numerator_value' => null,
            'denominator_value' => 100,
        ];

        $profile = (object) [
            'benchmarking_baseline' => 80,
        ];

        $result = $this->service->isTercapai($penilaian, $profile);

        expect($result)->toBe(false);
    });

    it('returns false for zero denominator', function () {
        $penilaian = (object) [
            'numerator_value' => 50,
            'denominator_value' => 0,
        ];

        $profile = (object) [
            'benchmarking_baseline' => 80,
        ];

        $result = $this->service->isTercapai($penilaian, $profile);

        expect($result)->toBe(false);
    });

    it('can count belum dinilai assessments', function () {
        $penilaians = collect([
            (object) ['numerator_value' => null, 'denominator_value' => null],
            (object) ['numerator_value' => 50, 'denominator_value' => 100],
            (object) ['numerator_value' => null, 'denominator_value' => null],
        ]);

        $count = $this->service->countBelumDinilai($penilaians);

        expect($count)->toBe(2);
    });

    it('can count unit melapor', function () {
        $penilaians = collect([
            (object) ['numerator_value' => 50, 'denominator_value' => 100, 'unit_kerja_id' => 1],
            (object) ['numerator_value' => 60, 'denominator_value' => 100, 'unit_kerja_id' => 1], // Same unit
            (object) ['numerator_value' => 70, 'denominator_value' => 100, 'unit_kerja_id' => 2],
            (object) ['numerator_value' => null, 'denominator_value' => null, 'unit_kerja_id' => 3], // Not reported
        ]);

        $count = $this->service->countUnitMelapor($penilaians);

        expect($count)->toBe(2); // Only units 1 and 2
    });

    it('can get latest profile IDs from indicators', function () {
        $indicators = collect([
            (object) ['id' => 1],
            (object) ['id' => 2],
            (object) ['id' => 1], // Duplicate
            (object) ['id' => 3],
        ]);

        $profileIds = $this->service->getLatestProfileIds($indicators);

        expect($profileIds->toArray())->toBe([1, 2, 3]);
    });

    it('can calculate dashboard stats', function () {
        $indikatorAktif = collect([
            1 => collect([
                (object) ['id' => 1, 'benchmarking_baseline' => 80],
                (object) ['id' => 2, 'benchmarking_baseline' => 75],
            ])
        ]);

        $penilaianByProfile = collect([
            1 => collect([
                (object) ['numerator_value' => 85, 'denominator_value' => 100], // Achieved
            ]),
            2 => collect([
                (object) ['numerator_value' => 70, 'denominator_value' => 100], // Not achieved
            ]),
        ]);

        $allPenilaian = collect([
            (object) ['numerator_value' => 85, 'denominator_value' => 100, 'unit_kerja_id' => 1],
            (object) ['numerator_value' => 70, 'denominator_value' => 100, 'unit_kerja_id' => 2],
            (object) ['numerator_value' => null, 'denominator_value' => null, 'unit_kerja_id' => 3],
        ]);

        $stats = $this->service->calculateDashboardStats(
            $indikatorAktif,
            $penilaianByProfile,
            $allPenilaian,
            1
        );

        expect($stats['totalIndikator'])->toBe(2);
        expect($stats['tercapai'])->toBe(1);
        expect($stats['unitMelapor'])->toBe(2);
        expect($stats['belumDinilai'])->toBe(1);
        expect($stats['achievementRate'])->toBe(50.0);
    });
});
