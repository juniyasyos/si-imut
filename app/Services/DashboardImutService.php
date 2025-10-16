<?php

namespace App\Services;

use App\Domains\Reporting\Presenters\LaporanCardPresenter;
use App\Facades\LaporanImut as LaporanImutFacade;
use App\Domains\Reporting\Models\LaporanImut;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola data dashboard mutu (Siimut).
 */
class DashboardImutService
{
    public function __construct(private readonly LaporanCardPresenter $cardPresenter)
    {
    }

    /**
     * Mengambil ID laporan terbaru, menggunakan cache jika tersedia.
     */
    public function getLatestLaporanId(): int
    {
        try {
            return LaporanImutFacade::getLatestLaporanId();
        } catch (\Throwable $e) {
            Log::error('Gagal mendapatkan latest laporan ID.', ['exception' => $e]);

            return 0;
        }
    }

    /**
     * Mengambil semua data dashboard utama, dengan cache dan fallback saat data tidak ditemukan.
     */
    public function getAllDashboardData(): array
    {
        $latestLaporanId = $this->getLatestLaporanId();
        if ($latestLaporanId === 0) {
            Log::warning('Tidak ditemukan laporan terbaru.');
        }

        $cacheKey = CacheKey::dashboardSiimutAllData($latestLaporanId);

        // return Cache::remember($cacheKey, now()->addDays(7), function () use ($latestLaporanId) {
        $laporan = LaporanImut::find($latestLaporanId);

        if (! $laporan) {
            Log::info('LaporanImut dengan ID tidak ditemukan.', ['id' => $latestLaporanId]);

            return $this->getEmptyDashboardData();
        }

        try {
            $currentPeriodData = LaporanImutFacade::getCurrentLaporanData($laporan);
            $chartData = LaporanImutFacade::getChartDataForLastLaporan(6);

            return array_merge($currentPeriodData, ['chart' => $chartData]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil data dashboard.', ['exception' => $e]);

            return $this->getEmptyDashboardData();
        }
        // });
    }

    /**
     * Mengembalikan konfigurasi statistik untuk tampilan dashboard.
     */
    public function getStatsConfig(array $data): array
    {
        return $this->cardPresenter->present($data);
    }

    /**
     * Menghasilkan deskripsi tren informatif dari data chart.
     */
    /**
     * Format nilai dengan formatter opsional.
     *
     * @param  mixed  $value
     * @param  callable|null  $formatter
     */
    public function formatValue($value, $formatter = null): string
    {
        return is_callable($formatter) ? $formatter($value) : (string) $value;
    }

    /**
     * Data default jika laporan tidak tersedia.
     */
    protected function getEmptyDashboardData(): array
    {
        return [
            'totalIndikator' => 0,
            'tercapai' => 0,
            'unitMelapor' => 0,
            'totalUnit' => 0,
            'belumDinilai' => 0,
            'chart' => [
                'tercapai' => [],
                'unitMelapor' => [],
                'belumDinilai' => [],
            ],
        ];
    }
}
