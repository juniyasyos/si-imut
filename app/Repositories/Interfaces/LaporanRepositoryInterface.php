<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface untuk Laporan data operations
 */
interface LaporanRepositoryInterface
{
    /**
     * Mengambil laporan berdasarkan unit kerja dengan jumlah penilaian dan persentase pengisian.
     *
     * @param int $laporanId
     * @return Builder
     */
    public function getReportByUnitKerja(int $laporanId): Builder;

    /**
     * Mengambil laporan berdasarkan data IMUT dengan total nilai dan persentase.
     *
     * @param int $laporanId
     * @return Builder
     */
    public function getReportByImutData(int $laporanId): Builder;

    /**
     * Mengambil detail laporan berdasarkan unit kerja tertentu.
     *
     * @param int $laporanId
     * @param int $unitKerjaId
     * @return Builder
     */
    public function getReportByUnitKerjaDetails(int $laporanId, int $unitKerjaId): Builder;

    /**
     * Mengambil detail laporan berdasarkan data IMUT tertentu dengan validasi unit kerja.
     *
     * @param int $laporanId
     * @param int $imutDataId
     * @param int|null $unitKerjaId
     * @return Builder
     */
    public function getReportByImutDataDetails(int $laporanId, int $imutDataId, ?int $unitKerjaId = null): Builder;

    /**
     * Mengambil laporan berdasarkan IMUT data dan unit kerja (multi-period)
     *
     * @param int $imutDataId
     * @param int $unitKerjaId
     * @return Builder
     */
    public function getLaporanByUnitKerjaDetails(int $imutDataId, int $unitKerjaId): Builder;

    /**
     * Mengambil summary berdasarkan IMUT data dengan benchmarking (grouped by laporan)
     *
     * @param int $imutDataId
     * @return Builder
     */
    public function getSummaryByImutDataGrouped(int $imutDataId): Builder;
}
