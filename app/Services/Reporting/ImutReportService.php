<?php

namespace App\Services\Reporting;

use App\Repositories\Interfaces\LaporanRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service untuk menangani report data di IMUT
 * Memisahkan query logic dari view/Livewire components
 */
class ImutReportService
{
    public function __construct(
        protected LaporanRepositoryInterface $laporanRepository
    ) {}

    /**
     * Mengambil summary laporan berdasarkan unit kerja
     */
    public function getUnitKerjaSummaryData(int $laporanId): Builder
    {
        return $this->laporanRepository->getReportByUnitKerja($laporanId);
    }

    /**
     * Mengambil summary laporan berdasarkan IMUT data
     */
    public function getImutDataSummaryData(int $laporanId): Builder
    {
        return $this->laporanRepository->getReportByImutData($laporanId);
    }

    /**
     * Mengambil detail laporan berdasarkan unit kerja tertentu
     */
    public function getUnitKerjaDetailData(int $laporanId, int $unitKerjaId): Builder
    {
        return $this->laporanRepository->getReportByUnitKerjaDetails($laporanId, $unitKerjaId);
    }

    /**
     * Mengambil detail laporan berdasarkan IMUT data tertentu
     */
    public function getImutDataDetailData(int $laporanId, int $imutDataId): Builder
    {
        return $this->laporanRepository->getReportByImutDataDetails($laporanId, $imutDataId);
    }

    /**
     * Mengambil laporan overview data untuk table
     */
    public function getImutDataOverviewData(int $imutDataId, int $unitKerjaId): Builder
    {
        return $this->laporanRepository->getLaporanByUnitKerjaDetails($imutDataId, $unitKerjaId);
    }

    /**
     * Mengambil summary grouped by imut data
     */
    public function getImutDataGroupedSummary(int $imutDataId): Builder
    {
        return $this->laporanRepository->getSummaryByImutDataGrouped($imutDataId);
    }

    /**
     * Mengambil summary laporan untuk ImutDataSummaryTable
     */
    public function getSummaryForImutDataTable(int $imutDataId): Builder
    {
        return $this->getImutDataGroupedSummary($imutDataId);
    }

    /**
     * Mengambil data untuk ImutDataUnitKerjaTable
     */
    public function getDataForUnitKerjaTable(int $imutDataId, int $unitKerjaId): Builder
    {
        return $this->getImutDataOverviewData($imutDataId, $unitKerjaId);
    }
}
