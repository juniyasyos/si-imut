<?php

namespace App\Modules\ImutMaster\Models;

use App\Models\LaporanImut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model untuk tracking profil yang digunakan dalam laporan
 *
 * @property int $id
 * @property int $laporan_imut_id
 * @property int $imut_data_id
 * @property int $imut_profil_id
 * @property \Carbon\Carbon $selected_at
 * @property array|null $selection_metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read LaporanImut $laporanImut
 * @property-read ImutData $imutData
 * @property-read ImutProfile $imutProfile
 */
class LaporanImutProfile extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'laporan_imut_profiles';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'laporan_imut_id',
        'imut_data_id',
        'imut_profil_id',
        'selected_at',
        'selection_metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'selected_at' => 'datetime',
        'selection_metadata' => 'array',
    ];

    /**
     * Relasi ke LaporanImut
     */
    public function laporanImut(): BelongsTo
    {
        return $this->belongsTo(LaporanImut::class);
    }

    /**
     * Relasi ke ImutData
     */
    public function imutData(): BelongsTo
    {
        return $this->belongsTo(ImutData::class);
    }

    /**
     * Relasi ke ImutProfile
     */
    public function imutProfile(): BelongsTo
    {
        return $this->belongsTo(ImutProfile::class, 'imut_profil_id');
    }

    /**
     * Scope untuk laporan tertentu
     */
    public function scopeForLaporan($query, int $laporanId)
    {
        return $query->where('laporan_imut_id', $laporanId);
    }

    /**
     * Scope untuk imut data tertentu
     */
    public function scopeForImutData($query, int $imutDataId)
    {
        return $query->where('imut_data_id', $imutDataId);
    }
}
