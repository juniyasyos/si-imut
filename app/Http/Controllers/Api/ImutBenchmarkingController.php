<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Benchmarking\ImutBenchmarkingService;
use App\Models\ImutData;
use App\Models\RegionType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImutBenchmarkingController extends Controller
{
    protected ImutBenchmarkingService $benchmarkingService;

    public function __construct(ImutBenchmarkingService $benchmarkingService)
    {
        $this->benchmarkingService = $benchmarkingService;
    }

    /**
     * Mendapatkan data benchmark untuk chart
     *
     * @param Request $request
     * @param int $imutDataId
     * @return JsonResponse
     */
    public function getChartData(Request $request, int $imutDataId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2030',
                'region_type_ids' => 'sometimes|array',
                'region_type_ids.*' => 'integer|exists:region_types,id',
            ]);

            $imutData = ImutData::with('categories')->findOrFail($imutDataId);

            if (!$imutData->categories->is_benchmark_category) {
                return response()->json([
                    'success' => false,
                    'message' => 'IMUT Data ini tidak memiliki kategori benchmark',
                ], 400);
            }

            $year = $validated['year'];
            $regionTypeIds = $validated['region_type_ids'] ?? null;

            $chartData = $this->benchmarkingService->getBenchmarkChartData(
                $imutDataId,
                $year,
                $regionTypeIds
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'imut_data' => [
                        'id' => $imutData->id,
                        'title' => $imutData->title,
                    ],
                    'year' => $year,
                    'chart_data' => $chartData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data benchmark',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Mendapatkan data benchmark untuk debugging
     *
     * @param Request $request
     * @param int $imutDataId
     * @return JsonResponse
     */
    public function getDebugData(Request $request, int $imutDataId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2030',
                'month' => 'sometimes|integer|min:1|max:12',
            ]);

            $imutData = ImutData::with(['categories', 'benchmarkings.regionType'])
                ->findOrFail($imutDataId);

            $year = $validated['year'];
            $month = $validated['month'] ?? null;

            // Get all benchmarks for this IMUT Data
            $query = $imutData->benchmarkings()->with('regionType');

            if ($month) {
                $date = \Carbon\Carbon::create($year, $month, 1);
                $query->whereRaw('? BETWEEN period_start AND COALESCE(period_end, ?)', [$date, $date]);
            } else {
                $query->whereYear('period_start', '<=', $year);
            }

            $benchmarks = $query->get();

            return response()->json([
                'success' => true,
                'debug' => true,
                'data' => [
                    'imut_data' => [
                        'id' => $imutData->id,
                        'title' => $imutData->title,
                        'is_benchmark_category' => $imutData->categories->is_benchmark_category,
                    ],
                    'query_params' => [
                        'year' => $year,
                        'month' => $month,
                    ],
                    'total_benchmarks' => $benchmarks->count(),
                    'benchmarks' => $benchmarks->map(function ($benchmark) {
                        return [
                            'id' => $benchmark->id,
                            'region_type' => $benchmark->regionType->type,
                            'region_name' => $benchmark->region_name,
                            'benchmark_value' => $benchmark->benchmark_value,
                            'period_start' => $benchmark->period_start->format('Y-m-d'),
                            'period_end' => $benchmark->period_end?->format('Y-m-d'),
                            'is_active' => $benchmark->is_active,
                            'year' => $benchmark->year,
                            'month' => $benchmark->month,
                        ];
                    }),
                    'region_types_available' => RegionType::all(['id', 'type']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat debugging data benchmark',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Mendapatkan statistik coverage benchmark
     *
     * @return JsonResponse
     */
    public function getCoverage(): JsonResponse
    {
        try {
            $coverage = $this->benchmarkingService->getBenchmarkCoverage();

            return response()->json([
                'success' => true,
                'data' => $coverage,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik coverage',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Mendapatkan IMUT Data yang belum memiliki benchmark
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMissingBenchmarks(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'region_type_id' => 'sometimes|integer|exists:region_types,id',
            ]);

            $regionTypeId = $validated['region_type_id'] ?? null;
            $missingImutData = $this->benchmarkingService->getImutDataWithoutBenchmark($regionTypeId);

            return response()->json([
                'success' => true,
                'data' => [
                    'region_type_id' => $regionTypeId,
                    'missing_count' => $missingImutData->count(),
                    'imut_data' => $missingImutData->map(function ($imutData) {
                        return [
                            'id' => $imutData->id,
                            'title' => $imutData->title,
                            'category' => $imutData->categories->category_name,
                            'is_benchmark_category' => $imutData->categories->is_benchmark_category,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data IMUT yang belum memiliki benchmark',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Bulk create benchmarks
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'imut_data_ids' => 'required|array',
                'imut_data_ids.*' => 'integer|exists:imut_data,id',
                'region_type_id' => 'required|integer|exists:region_types,id',
                'benchmark_value' => 'required|numeric|min:0|max:100',
                'period_start' => 'required|date',
                'period_end' => 'sometimes|nullable|date|after_or_equal:period_start',
            ]);

            $createdCount = $this->benchmarkingService->bulkCreateBenchmarks(
                $validated['imut_data_ids'],
                $validated['region_type_id'],
                $validated['benchmark_value'],
                \Carbon\Carbon::parse($validated['period_start']),
                isset($validated['period_end']) ? \Carbon\Carbon::parse($validated['period_end']) : null
            );

            return response()->json([
                'success' => true,
                'message' => "Berhasil membuat {$createdCount} benchmark data",
                'data' => [
                    'created_count' => $createdCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat benchmark secara bulk',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
