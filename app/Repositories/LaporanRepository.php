<?php

namespace App\Repositories;

use App\Models\LaporanUnitKerja;
use App\QueryBuilders\ImutDataDetailReportQueryBuilder;
use App\QueryBuilders\ImutDataGroupedSummaryQueryBuilder;
use App\QueryBuilders\ImutDataReportQueryBuilder;
use App\QueryBuilders\LaporanByUnitQueryBuilder;
use App\QueryBuilders\UnitKerjaDetailReportQueryBuilder;
use App\QueryBuilders\UnitKerjaReportQueryBuilder;
use App\Repositories\Interfaces\LaporanRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Repository untuk LaporanUnitKerja
 * Mengabstraksi query logic dari model
 */
class LaporanRepository implements LaporanRepositoryInterface
{
    /**
     * Mengambil laporan berdasarkan unit kerja dengan jumlah penilaian dan persentase pengisian.
     */
    public function getReportByUnitKerja(int $laporanId): Builder
    {
        return (new UnitKerjaReportQueryBuilder())->build($laporanId);
    }

    /**
     * Mengambil laporan berdasarkan data IMUT dengan total nilai dan persentase.
     */
    public function getReportByImutData(int $laporanId): Builder
    {
        return (new ImutDataReportQueryBuilder())->build($laporanId);
    }

    /**
     * Mengambil detail laporan berdasarkan unit kerja tertentu.
     */
    public function getReportByUnitKerjaDetails(int $laporanId, int $unitKerjaId): Builder
    {
        return (new UnitKerjaDetailReportQueryBuilder())->build($laporanId, $unitKerjaId);
    }

    /**
     * Mengambil detail laporan berdasarkan data IMUT tertentu dengan validasi unit kerja.
     */
    public function getReportByImutDataDetails(int $laporanId, int $imutDataId, ?int $unitKerjaId = null): Builder
    {
        return (new ImutDataDetailReportQueryBuilder())->build($laporanId, $imutDataId, $unitKerjaId);
    }

    /**
     * Mengambil laporan berdasarkan IMUT data dan unit kerja (multi-period)
     */
    public function getLaporanByUnitKerjaDetails(int $imutDataId, int $unitKerjaId): Builder
    {
        return (new LaporanByUnitQueryBuilder())->build($imutDataId, $unitKerjaId);
    }

    /**
     * Mengambil summary berdasarkan IMUT data dengan benchmarking (grouped by laporan)
     */
    public function getSummaryByImutDataGrouped(int $imutDataId): Builder
    {
        return (new ImutDataGroupedSummaryQueryBuilder())->build($imutDataId);
    }
}
