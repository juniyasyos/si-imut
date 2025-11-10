<?php

namespace App\Filament\Resources\LaporanImutResource\Widgets;

use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutDataCompletionChart extends ApexChartWidget
{
    protected static ?string $chartId = 'imutDataCompletionChart';
    protected static ?string $heading = 'Kelengkapan Pelaporan Per Indikator Mutu';
        protected static ?int $sort = 2;

    public int|string|array|null $columnSpanCustom = null;
    public ?int $laporanId = null;

    /**
     * @return int | string | array<string, int | null>
     */
    public function getColumnSpan(): int | string | array
    {
        return $this->columnSpanCustom ?? 'full';
    }

    protected function getOptions(): array
    {
        if (!$this->laporanId) {
            return $this->getNoDataOptions();
        }

        $laporan = LaporanImut::find($this->laporanId);

        if (!$laporan) {
            return $this->getNoDataOptions();
        }

        // Hitung berapa indikator mutu yang dilaporkan oleh semua unit
        $stats = $this->getImutDataCompletionStats($this->laporanId);

        $complete = $stats['complete'];
        $incomplete = $stats['incomplete'];
        $total = $complete + $incomplete;

        if ($total === 0) {
            return $this->getNoDataOptions();
        }

        $completePercentage = round(($complete / $total) * 100, 1);
        $incompletePercentage = round(($incomplete / $total) * 100, 1);

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 350
            ],
            'series' => [$complete, $incomplete],
            'labels' => [
                "Dilaporkan Semua Unit ({$complete} indikator - {$completePercentage}%)",
                "Belum Lengkap ({$incomplete} indikator - {$incompletePercentage}%)"
            ],
            'colors' => ['#10b981', '#f59e0b'],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['#ffffff'],
            ],
            'legend' => [
                'show' => true,
                'position' => 'bottom',
                'horizontalAlign' => 'center',
                'fontSize' => '14px',
                'fontFamily' => 'inherit',
                'fontWeight' => 500,
                'labels' => [
                    'colors' => '#6b7280',
                ],
                'markers' => [
                    'width' => 12,
                    'height' => 12,
                    'radius' => 3,
                ],
                'itemMargin' => [
                    'horizontal' => 10,
                    'vertical' => 5,
                ],
            ],
        ];
    }

    /**
     * Menghitung statistik kelengkapan pelaporan per indikator mutu
     *
     * Indikator dianggap lengkap jika semua unit kerja yang DI-ASSIGN indikator tersebut
     * sudah melaporkan dengan nilai yang valid (numerator tidak null, denominator tidak null dan tidak 0)
     */
    protected function getImutDataCompletionStats(int $laporanId): array
    {
        $cacheKey = CacheKey::imutDataCompletionStats($laporanId);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($laporanId) {
            // Hitung total unit kerja yang terlibat dalam laporan
            $totalUnits = LaporanUnitKerja::where('laporan_imut_id', $laporanId)
                ->distinct('unit_kerja_id')
                ->count('unit_kerja_id');

            if ($totalUnits === 0) {
                return [
                    'complete' => 0,
                    'incomplete' => 0,
                ];
            }

            // Untuk setiap imut_data, hitung:
            // 1. Berapa unit yang seharusnya mengisi (expected_units) - dari imut_penilaians
            // 2. Berapa unit yang sudah mengisi dengan valid (filled_units)
            $imutDataStats = DB::table('imut_penilaians')
                ->join('laporan_unit_kerjas', 'imut_penilaians.laporan_unit_kerja_id', '=', 'laporan_unit_kerjas.id')
                ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
                ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
                ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
                ->select([
                    'imut_data.id as imut_data_id',
                    'imut_data.title',
                    // Total unit yang seharusnya mengisi indikator ini
                    DB::raw('COUNT(DISTINCT laporan_unit_kerjas.unit_kerja_id) as expected_units'),
                    // Unit yang sudah mengisi dengan nilai valid
                    DB::raw('COUNT(DISTINCT CASE
                        WHEN imut_penilaians.numerator_value IS NOT NULL
                        AND imut_penilaians.denominator_value IS NOT NULL
                        AND imut_penilaians.denominator_value != 0
                        THEN laporan_unit_kerjas.unit_kerja_id
                    END) as filled_units')
                ])
                ->groupBy('imut_data.id', 'imut_data.title')
                ->get();

            $detailData = [];
            $complete = 0;
            $incomplete = 0;

            // Hitung indikator yang sudah dilaporkan oleh semua unit yang seharusnya mengisi
            foreach ($imutDataStats as $stat) {
                // Lengkap jika filled_units = expected_units dan expected_units > 0
                $isComplete = $stat->expected_units > 0 && $stat->filled_units == $stat->expected_units;

                $detailData[] = [
                    'imut_data_id' => $stat->imut_data_id,
                    'title' => $stat->title,
                    'expected_units' => $stat->expected_units,
                    'filled_units' => $stat->filled_units,
                    'unfilled_units' => $stat->expected_units - $stat->filled_units,
                    'percentage' => $stat->expected_units > 0
                        ? round(($stat->filled_units / $stat->expected_units) * 100, 2)
                        : 0,
                    'status' => $isComplete ? 'LENGKAP' : 'TIDAK LENGKAP',
                ];

                if ($isComplete) {
                    $complete++;
                } else {
                    $incomplete++;
                }
            }

            return [
                'complete' => $complete,
                'incomplete' => $incomplete,
            ];
        });
    }

    protected function getNoDataOptions(): array
    {
        return [
            'chart' => [
                'type' => 'pie',
                'height' => 350,
            ],
            'series' => [],
            'labels' => [],
            'noData' => [
                'text' => 'Tidak ada data tersedia untuk laporan ini',
                'align' => 'center',
                'verticalAlign' => 'middle',
                'offsetX' => 0,
                'offsetY' => 0,
                'style' => [
                    'color' => '#9ca3af',
                    'fontSize' => '16px',
                    'fontFamily' => 'inherit',
                    'fontWeight' => 500,
                ],
            ],
        ];
    }
}
