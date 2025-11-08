<?php

namespace App\Filament\Resources\LaporanImutResource\Widgets;

use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class UnitKerjaCompletionChart extends ApexChartWidget
{
    protected static ?string $chartId = 'unitKerjaCompletionChart';
    protected static ?string $heading = 'Kelengkapan Pelaporan Per Unit Kerja';
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

        // Hitung berapa unit yang sudah lengkap melaporkan semua indikator mutunya
        $stats = $this->getUnitKerjaCompletionStats($this->laporanId);

        // dd($stats);

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
                'height' => 350,
            ],
            'series' => [$complete, $incomplete],
            'labels' => [
                "Lengkap ({$complete} unit - {$completePercentage}%)",
                "Tidak Lengkap ({$incomplete} unit - {$incompletePercentage}%)"
            ],
            'colors' => ['#10b981', '#dc2626'],
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

    protected function getUnitKerjaCompletionStats(int $laporanId): array
    {
        // Ambil semua unit kerja yang terlibat dalam laporan ini
        $unitKerjaStats = LaporanUnitKerja::query()
            ->where('laporan_imut_id', $laporanId)
            ->select([
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                'laporan_unit_kerjas.id as laporan_unit_kerja_id',
                DB::raw('COUNT(imut_penilaians.id) as total_indicators'),
                DB::raw('SUM(
                CASE
                    WHEN imut_penilaians.numerator_value IS NOT NULL
                    AND imut_penilaians.denominator_value IS NOT NULL
                    AND imut_penilaians.denominator_value != 0
                    THEN 1
                    ELSE 0
                END
            ) as filled_indicators'),
                DB::raw('SUM(
                CASE
                    WHEN imut_penilaians.numerator_value IS NOT NULL
                    AND imut_penilaians.denominator_value IS NOT NULL
                    THEN 1
                    ELSE 0
                END
            ) as filled_indicators_including_zero'),
            ])
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->groupBy('laporan_unit_kerjas.unit_kerja_id', 'unit_kerja.unit_name', 'laporan_unit_kerjas.id')
            ->get();

        // Group by unit_kerja_id untuk handle kasus multiple laporan_unit_kerja per unit
        $groupedStats = $unitKerjaStats->groupBy('unit_kerja_id');

        $detailData = [];
        $complete = 0;
        $incomplete = 0;

        foreach ($groupedStats as $unitKerjaId => $stats) {
            $totalIndicators = $stats->sum('total_indicators');
            $totalFilled     = $stats->sum('filled_indicators');
            $unitName        = $stats->first()->unit_name;

            $isComplete = $totalIndicators > 0 && $totalFilled == $totalIndicators;

            $detailData[] = [
                'unit_kerja_id'          => $unitKerjaId,
                'nama_unit'              => $unitName,
                'jumlah_imut_total'      => $totalIndicators,
                'jumlah_laporan_terisi'  => $totalFilled,
                'jumlah_belum_terisi'    => $totalIndicators - $totalFilled,
                'persentase_kelengkapan' => $totalIndicators > 0
                    ? round(($totalFilled / $totalIndicators) * 100, 2) . '%'
                    : '0%',
                'status'                 => $isComplete ? 'LENGKAP' : 'TIDAK LENGKAP',
            ];

            if ($isComplete) {
                $complete++;
            } else {
                $incomplete++;
            }
        }

        return [
            'complete'          => $complete,
            'incomplete'        => $incomplete,
            'laporan_id'        => $laporanId,
            'total_unit_kerja'  => count($detailData),
            'unit_lengkap'      => $complete,
            'unit_tidak_lengkap' => $incomplete,
            'detail_per_unit'   => $detailData,
        ];
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
