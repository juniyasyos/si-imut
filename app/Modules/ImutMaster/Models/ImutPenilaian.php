<?php

namespace App\Modules\ImutMaster\Models;

use App\Models\LaporanUnitKerja;
use App\Models\FieldResponse;
use App\Models\RegionType;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ImutPenilaian extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ImutPenilaianFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imut_profil_id',
        'laporan_unit_kerja_id',
        'analysis',
        'recommendations',
        'numerator_value',
        'denominator_value',
        'is_auto_calculated',
        'calculation_metadata',
    ];

    /**
     * The attributes that are guarded.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_auto_calculated' => 'boolean',
        'calculation_metadata' => 'array',
    ];




    /**
     * Get the options for logging activity.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Get the profile that owns the ImutPenilaian
     *
     * @return void
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(ImutProfile::class, 'imut_profil_id');
    }

    /**
     * Get the unit kerja that owns the ImutPenilaian
     *
     * @return void
     */
    public function laporanUnitKerja(): BelongsTo
    {
        return $this->belongsTo(LaporanUnitKerja::class);
    }

    public function profileById($profileId): HasOne
    {
        return $this->hasOne(ImutProfile::class)->where('id', $profileId);
    }

    public function fieldResponses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FieldResponse::class, 'imut_penilaian_id');
    }
}
