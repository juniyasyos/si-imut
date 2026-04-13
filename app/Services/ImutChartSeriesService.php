<?php

namespace App\Services;

use App\Models\ImutCategory;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;

class ImutChartSeriesService
{
    protected array $colorThemes = [
        'modern' => [
            '#6366f1',
            '#10b981',
            '#f59e0b',
            '#3b82f6',
            '#8b5cf6',
            '#06b6d4',
            '#eab308',
            '#ef4444',
            '#0ea5e9',
            '#22c55e',
        ],
    ];

    public function getDefaultColors(): array
    {
        return $this->colorThemes['modern'];
    }

    public function getCategories(): array
    {
        return Cache::remember('imut:categories:short_names', now()->addDay(), function () {
            return ImutCategory::orderBy('short_name')->pluck('short_name')->toArray();
        });
    }

    public function buildSeries($laporans, ?array $formData): array
    {
        $categories = $this->getCategories();
        $colors = $this->getDefaultColors();

        $dataPerKategori = $this->calculateAchievementData(collect($laporans), $categories);

        return collect($categories)->map(function ($shortName, $i) use ($formData, $dataPerKategori, $colors) {
            return [
                'name'  => $shortName,
                'type'  => $formData['series_types'][$shortName] ?? 'column',
                'data'  => $dataPerKategori[$shortName] ?? [],
                'color' => $formData['series_colors'][$shortName] ?? $colors[$i % count($colors)],
            ];
        })->values()->toArray();
    }

    public function calculateAchievementData($laporans, array $categories): array
    {
        $laporans = collect($laporans);
        $data = [];

        // Init semua kategori dengan array kosong sejumlah laporan
        foreach ($categories as $shortName) {
            $data[$shortName] = array_fill(0, $laporans->count(), 0);
        }

        foreach ($laporans as $i => $laporan) {
            $laporanId = $laporan->id;

            $cached = Cache::get(CacheKey::imutChartSeriesData($laporanId));
            if ($cached) {
                foreach ($categories as $shortName) {
                    $data[$shortName][$i] = $cached[$shortName] ?? 0;
                }
                continue;
            }

            // Hitung manual jika belum ada cache
            $result = [];

            foreach ($laporan->laporanUnitKerjas as $unitKerja) {
                foreach ($unitKerja->imutPenilaians as $penilaian) {
                    $profile = $penilaian->profile;
                    $category = $profile?->imutData?->categories;

                    if (! $category || ! $category->short_name || $penilaian->denominator_value == 0) {
                        continue;
                    }

                    $shortName = $category->short_name;
                    $nilai = ceil(($penilaian->numerator_value / $penilaian->denominator_value) * 100 * 100) / 100;

                    if ($nilai >= $profile->target_value) {
                        $result[$shortName] = ($result[$shortName] ?? 0) + 1;
                    }
                }
            }

            // Cache per-laporan selama 7 hari
            Cache::put(CacheKey::imutChartSeriesData($laporanId), $result, now()->addDays(7));

            foreach ($categories as $shortName) {
                $data[$shortName][$i] = $result[$shortName] ?? 0;
            }
        }

        return $data;
    }
}
