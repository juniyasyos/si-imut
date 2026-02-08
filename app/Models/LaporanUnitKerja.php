<?php

namespace App\Models;

use App\QueryBuilders\ImutDataDetailReportQueryBuilder;
use App\QueryBuilders\ImutDataGroupedSummaryQueryBuilder;
use App\QueryBuilders\ImutDataReportQueryBuilder;
use App\QueryBuilders\LaporanByUnitQueryBuilder;
use App\QueryBuilders\UnitKerjaDetailReportQueryBuilder;
use App\QueryBuilders\UnitKerjaReportQueryBuilder;
use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Model untuk mengelola laporan unit kerja.
 */
class LaporanUnitKerja extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Relasi ke model LaporanImut.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function laporanImut()
    {
        return $this->belongsTo(LaporanImut::class);
    }

    /**
     * Relasi ke model UnitKerja.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * Relasi ke model ImutPenilaian.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imutPenilaians()
    {
        return $this->hasMany(ImutPenilaian::class, 'laporan_unit_kerja_id');
    }

    /**
     * Hook model saat disimpan atau dihapus untuk menghapus cache terkait.
     */
    protected static function booted()
    {
        static::saved(fn($laporan) => $laporan->clearCache());
        static::deleted(fn($laporan) => $laporan->clearCache());
    }

    /**
     * Menghapus cache yang berkaitan dengan laporan ini.
     */
    public function clearCache(): void
    {
        $laporanId = $this->laporan_imut_id;
        $unitKerjaId = $this->unit_kerja_id;

        Cache::forget(CacheKey::laporanUnitDetail($laporanId, $unitKerjaId));
        Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));

        // Clear completion chart cache
        Cache::forget(CacheKey::unitKerjaCompletionStats($laporanId));
        Cache::forget(CacheKey::imutDataCompletionStats($laporanId));
    }

    /**
     * Query scope untuk filter berdasarkan laporan
     */
    public function scopeForLaporan(Builder $query, int $laporanId): Builder
    {
        return $query->where('laporan_unit_kerjas.laporan_imut_id', $laporanId);
    }

    /**
     * Query scope untuk filter berdasarkan unit kerja
     */
    public function scopeForUnitKerja(Builder $query, int $unitKerjaId): Builder
    {
        return $query->where('laporan_unit_kerjas.unit_kerja_id', $unitKerjaId);
    }

    /**
     * Mengambil laporan berdasarkan unit kerja dengan jumlah penilaian dan persentase pengisian.
     *
     * @param  int  $laporanId
     * @return Builder
     */
    public static function getReportByUnitKerja(int $laporanId): Builder
    {
        return (new UnitKerjaReportQueryBuilder())->build($laporanId);
    }

    /**
     * Mengambil laporan berdasarkan data IMUT dengan total nilai dan persentase.
     *
     * @param int $laporanId
     * @return Builder
     */
    public static function getReportByImutData(int $laporanId): Builder
    {
        return (new ImutDataReportQueryBuilder())->build($laporanId);
    }

    /**
     * Mengambil detail laporan berdasarkan unit kerja tertentu.
     *
     * @param int $laporanId
     * @param int $unitKerjaId
     * @return Builder
     */
    public static function getReportByUnitKerjaDetails(int $laporanId, int $unitKerjaId): Builder
    {
        return (new UnitKerjaDetailReportQueryBuilder())->build($laporanId, $unitKerjaId);
    }

    /**
     * Mengambil detail laporan berdasarkan data IMUT tertentu dengan validasi unit kerja.
     *
     * @param int $laporanId
     * @param int $imutDataId
     * @param int|null $unitKerjaId - Validasi tambahan untuk memastikan data sesuai unit kerja
     * @return Builder
     */
    public static function getReportByImutDataDetails(int $laporanId, int $imutDataId, ?int $unitKerjaId = null): Builder
    {
        return (new ImutDataDetailReportQueryBuilder())->build($laporanId, $imutDataId, $unitKerjaId);
    }

    /**
     * Mengambil laporan berdasarkan IMUT data dan unit kerja (multi-period)
     *
     * @param int $imutDataId
     * @param int $unitKerjaId
     * @return Builder
     */
    public static function getLaporanByUnitKerjaDetails(int $imutDataId, int $unitKerjaId): Builder
    {
        return (new LaporanByUnitQueryBuilder())->build($imutDataId, $unitKerjaId);
    }

    /**
     * Mengambil summary berdasarkan IMUT data dengan benchmarking (grouped by laporan)
     *
     * @param int $imutDataId
     * @return Builder
     */
    public static function getSummaryByImutDataGrouped(int $imutDataId): Builder
    {
        return (new ImutDataGroupedSummaryQueryBuilder())->build($imutDataId);
    }
}
