<?php

namespace App\Models;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Model untuk mengelola laporan unit kerja.
 *
 * Query methods sudah dipindahkan ke LaporanRepository
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

}
