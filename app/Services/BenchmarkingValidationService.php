<?php

namespace App\Services;

use App\Models\ImutBenchmarking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Service untuk validasi business logic benchmarking
 */
class BenchmarkingValidationService
{
    /**
     * Validasi apakah ada duplikasi benchmarking
     *
     * @param int $imutDataId
     * @param int $regionTypeId
     * @param int $year
     * @param int $month
     * @param int|null $ignoreBenchmarkingId ID yang akan diabaikan (untuk update)
     * @return array ['valid' => bool, 'message' => string|null, 'existing' => ImutBenchmarking|null]
     */
    public function validateDuplicate(
        int $imutDataId,
        int $regionTypeId,
        int $year,
        int $month,
        ?int $ignoreBenchmarkingId = null
    ): array {
        $query = ImutBenchmarking::query()
            ->where('imut_data_id', $imutDataId)
            ->where('region_type_id', $regionTypeId)
            ->where('year', $year)
            ->where('month', $month);

        if ($ignoreBenchmarkingId) {
            $query->where('id', '!=', $ignoreBenchmarkingId);
        }

        $existing = $query->first();

        if ($existing) {
            return [
                'valid' => false,
                'message' => "Benchmarking untuk indikator ID {$imutDataId}, region {$regionTypeId}, tahun {$year}, bulan {$month} sudah ada.",
                'existing' => $existing,
            ];
        }

        return [
            'valid' => true,
            'message' => null,
            'existing' => null,
        ];
    }

    /**
     * Validasi apakah ada overlapping period
     *
     * @param int $imutDataId
     * @param int $regionTypeId
     * @param Carbon|string $periodStart
     * @param Carbon|string|null $periodEnd
     * @param int|null $ignoreBenchmarkingId
     * @return array ['valid' => bool, 'message' => string|null, 'overlapping' => Collection]
     */
    public function validatePeriodOverlap(
        int $imutDataId,
        int $regionTypeId,
        Carbon|string $periodStart,
        Carbon|string|null $periodEnd = null,
        ?int $ignoreBenchmarkingId = null
    ): array {
        $periodStart = $periodStart instanceof Carbon ? $periodStart : Carbon::parse($periodStart);
        $periodEnd = $periodEnd ? ($periodEnd instanceof Carbon ? $periodEnd : Carbon::parse($periodEnd)) : null;

        $query = ImutBenchmarking::query()
            ->where('imut_data_id', $imutDataId)
            ->where('region_type_id', $regionTypeId)
            ->where('is_active', true);

        if ($ignoreBenchmarkingId) {
            $query->where('id', '!=', $ignoreBenchmarkingId);
        }

        // Check for period overlap
        $overlapping = $query->where(function ($q) use ($periodStart, $periodEnd) {
            // Case 1: New period starts within existing period
            $q->where(function ($q2) use ($periodStart) {
                $q2->where('period_start', '<=', $periodStart)
                    ->where(function ($q3) use ($periodStart) {
                        $q3->whereNull('period_end')
                            ->orWhere('period_end', '>=', $periodStart);
                    });
            })
            // Case 2: New period ends within existing period (if end date is set)
            ->orWhere(function ($q2) use ($periodEnd) {
                if ($periodEnd) {
                    $q2->where('period_start', '<=', $periodEnd)
                        ->where(function ($q3) use ($periodEnd) {
                            $q3->whereNull('period_end')
                                ->orWhere('period_end', '>=', $periodEnd);
                        });
                }
            })
            // Case 3: New period completely encompasses existing period
            ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                if ($periodEnd) {
                    $q2->where('period_start', '>=', $periodStart)
                        ->where(function ($q3) use ($periodEnd) {
                            $q3->where('period_end', '<=', $periodEnd)
                                ->orWhereNull('period_end');
                        });
                }
            })
            // Case 4: Existing period encompasses new period
            ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                $q2->where('period_start', '<=', $periodStart);
                if ($periodEnd) {
                    $q2->where('period_end', '>=', $periodEnd);
                } else {
                    $q2->whereNull('period_end');
                }
            });
        })->get();

        if ($overlapping->isNotEmpty()) {
            $messages = $overlapping->map(function ($item) {
                $start = $item->period_start?->format('Y-m-d') ?? 'N/A';
                $end = $item->period_end?->format('Y-m-d') ?? 'selamanya';
                return "ID {$item->id}: {$start} - {$end} (nilai: {$item->benchmark_value}%)";
            })->join(', ');

            return [
                'valid' => false,
                'message' => "Periode benchmarking bertumpang tindih dengan: {$messages}",
                'overlapping' => $overlapping,
            ];
        }

        return [
            'valid' => true,
            'message' => null,
            'overlapping' => collect(),
        ];
    }

    /**
     * Validasi nilai benchmark (harus antara 0-100 atau 0-120)
     *
     * @param float $value
     * @param float $min Default 0
     * @param float $max Default 120
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateBenchmarkValue(
        float $value,
        float $min = 0,
        float $max = 120
    ): array {
        if ($value < $min || $value > $max) {
            return [
                'valid' => false,
                'message' => "Nilai benchmark harus antara {$min}% dan {$max}%. Nilai yang diberikan: {$value}%",
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    /**
     * Validasi periode (period_start harus sebelum period_end)
     *
     * @param Carbon|string $periodStart
     * @param Carbon|string|null $periodEnd
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validatePeriodLogic(
        Carbon|string $periodStart,
        Carbon|string|null $periodEnd = null
    ): array {
        $periodStart = $periodStart instanceof Carbon ? $periodStart : Carbon::parse($periodStart);

        if ($periodEnd) {
            $periodEnd = $periodEnd instanceof Carbon ? $periodEnd : Carbon::parse($periodEnd);

            if ($periodEnd->lte($periodStart)) {
                return [
                    'valid' => false,
                    'message' => "Tanggal akhir periode ({$periodEnd->format('Y-m-d')}) harus setelah tanggal mulai ({$periodStart->format('Y-m-d')}).",
                ];
            }
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    /**
     * Validasi konsistensi tahun/bulan dengan period_start
     *
     * @param int $year
     * @param int $month
     * @param Carbon|string $periodStart
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateYearMonthConsistency(
        int $year,
        int $month,
        Carbon|string $periodStart
    ): array {
        $periodStart = $periodStart instanceof Carbon ? $periodStart : Carbon::parse($periodStart);

        if ($periodStart->year !== $year || $periodStart->month !== $month) {
            return [
                'valid' => false,
                'message' => "Tahun ({$year}) dan bulan ({$month}) tidak konsisten dengan period_start ({$periodStart->format('Y-m')}).",
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    /**
     * Validasi apakah benchmarking masih aktif untuk tanggal tertentu
     *
     * @param ImutBenchmarking $benchmarking
     * @param Carbon|string|null $date Default: now
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateActiveForDate(
        ImutBenchmarking $benchmarking,
        Carbon|string|null $date = null
    ): array {
        $date = $date ? ($date instanceof Carbon ? $date : Carbon::parse($date)) : now();

        if (!$benchmarking->is_active) {
            return [
                'valid' => false,
                'message' => "Benchmarking ID {$benchmarking->id} tidak aktif.",
            ];
        }

        if (!$benchmarking->isValidForPeriod($date)) {
            return [
                'valid' => false,
                'message' => "Benchmarking ID {$benchmarking->id} tidak berlaku untuk tanggal {$date->format('Y-m-d')}.",
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    /**
     * Validasi lengkap untuk create/update benchmarking
     *
     * @param array $data Data yang akan divalidasi
     * @param int|null $ignoreBenchmarkingId ID yang akan diabaikan (untuk update)
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array]
     */
    public function validateComplete(array $data, ?int $ignoreBenchmarkingId = null): array
    {
        $errors = [];
        $warnings = [];

        // Required fields check
        $requiredFields = ['imut_data_id', 'region_type_id', 'year', 'month', 'benchmark_value', 'period_start'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null) {
                $errors[] = "Field '{$field}' wajib diisi.";
            }
        }

        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Validate benchmark value
        $valueValidation = $this->validateBenchmarkValue($data['benchmark_value']);
        if (!$valueValidation['valid']) {
            $errors[] = $valueValidation['message'];
        }

        // Validate period logic
        $periodLogic = $this->validatePeriodLogic(
            $data['period_start'],
            $data['period_end'] ?? null
        );
        if (!$periodLogic['valid']) {
            $errors[] = $periodLogic['message'];
        }

        // Validate year/month consistency
        $consistency = $this->validateYearMonthConsistency(
            $data['year'],
            $data['month'],
            $data['period_start']
        );
        if (!$consistency['valid']) {
            $warnings[] = $consistency['message'];
        }

        // Validate duplicate
        $duplicate = $this->validateDuplicate(
            $data['imut_data_id'],
            $data['region_type_id'],
            $data['year'],
            $data['month'],
            $ignoreBenchmarkingId
        );
        if (!$duplicate['valid']) {
            $errors[] = $duplicate['message'];
        }

        // Validate period overlap
        $overlap = $this->validatePeriodOverlap(
            $data['imut_data_id'],
            $data['region_type_id'],
            $data['period_start'],
            $data['period_end'] ?? null,
            $ignoreBenchmarkingId
        );
        if (!$overlap['valid']) {
            $errors[] = $overlap['message'];
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get all active benchmarking for specific indicator and region
     *
     * @param int $imutDataId
     * @param int $regionTypeId
     * @return Collection
     */
    public function getActiveBenchmarkings(int $imutDataId, int $regionTypeId): Collection
    {
        return ImutBenchmarking::query()
            ->forIndicator($imutDataId)
            ->forRegion($regionTypeId)
            ->where('is_active', true)
            ->orderBy('period_start')
            ->get();
    }

    /**
     * Get benchmarking statistics for reporting
     *
     * @param int|null $imutDataId
     * @param int|null $regionTypeId
     * @return array
     */
    public function getBenchmarkingStats(?int $imutDataId = null, ?int $regionTypeId = null): array
    {
        $query = ImutBenchmarking::query();

        if ($imutDataId) {
            $query->forIndicator($imutDataId);
        }

        if ($regionTypeId) {
            $query->forRegion($regionTypeId);
        }

        $total = $query->count();
        $active = (clone $query)->where('is_active', true)->count();
        $inactive = $total - $active;
        $withEndDate = (clone $query)->whereNotNull('period_end')->count();
        $permanent = (clone $query)->whereNull('period_end')->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'with_end_date' => $withEndDate,
            'permanent' => $permanent,
            'average_value' => $total > 0 ? round($query->avg('benchmark_value'), 2) : 0,
            'min_value' => $total > 0 ? $query->min('benchmark_value') : 0,
            'max_value' => $total > 0 ? $query->max('benchmark_value') : 0,
        ];
    }
}
