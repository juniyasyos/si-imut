<?php

namespace App\Models;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasUniqueWithSoftDeletes;

class ImutData extends Model
{
    /** @use HasFactory<\Database\Factories\ImutDataFactory> */
    use HasFactory, LogsActivity, SoftDeletes, HasUniqueWithSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'imut_kategori_id',
        'description',
        'status',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

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
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'status' => 'boolean',
        ];
    }

    public function clearCache()
    {
        $laporanUnitKerja = $this->laporanUnitKerja;

        if ($laporanUnitKerja) {
            $laporanId = $laporanUnitKerja->laporan_imut_id;
            $unitKerjaId = $laporanUnitKerja->unit_kerja_id;

            Cache::forget(CacheKey::laporanUnitDetail($laporanId, $unitKerjaId));

            Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));
            Cache::forget(CacheKey::dashboardSiimutChartData());
        }

        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
    }

    protected static function booted()
    {
        static::saved(fn($penilaian) => $penilaian->clearCache());
        static::deleted(fn($penilaian) => $penilaian->clearCache());
    }

    /**
     * Get the options for logging activity.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * func tion to get the category of the indicator
     *
     * @return void
     */
    public function categories(): BelongsTo
    {
        return $this->belongsTo(ImutCategory::class, 'imut_kategori_id');
    }

    /**
     * function to get the benchmarking of the indicator
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(ImutProfile::class);
    }

    /**
     * function to get the notes of the indicator
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ImutDataNote::class);
    }

    /**
     * function to get the benchmarking of the indicator
     */
    public function unitKerja(): BelongsToMany
    {
        return $this->belongsToMany(UnitKerja::class, 'imut_data_unit_kerja')
            ->using(ImutDataUnitKerja::class)
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Get all region types that belong to this ImutData.
     */
    public function regionTypes(): HasMany
    {
        return $this->hasMany(RegionType::class);
    }

    /**
     * Get the related Imut Bencmarking.
     */
    public function benchmarkings(): HasMany
    {
        return $this->hasMany(related: ImutBenchmarking::class);
    }

    // Di model ImutData
    public function latestProfile()
    {
        return $this->hasOne(ImutProfile::class)->latestOfMany('version');
    }

    /**
     * Dapatkan profil yang valid pada tanggal tertentu
     */
    public function profileValidOnDate($date)
    {
        return $this->hasOne(ImutProfile::class)
            ->validOnDate($date)
            ->latestOfMany('version');
    }

    /**
     * Dapatkan profil yang valid untuk periode laporan
     */
    public function profileValidForPeriod($startDate, $endDate)
    {
        return $this->profiles()
            ->validForPeriod($startDate, $endDate)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Dapatkan profil yang tepat untuk laporan tertentu
     * Pertama cek apakah sudah ada profil yang dipilih khusus untuk laporan ini
     * Jika tidak ada, ambil profil yang valid untuk periode laporan
     */
    public function profileForLaporan($laporanImut)
    {
        // Cek apakah sudah ada profil yang dipilih khusus untuk laporan ini
        $selectedProfile = LaporanImutProfile::where('laporan_imut_id', $laporanImut->id)
            ->where('imut_data_id', $this->id)
            ->with('imutProfile')
            ->first();

        if ($selectedProfile) {
            return $selectedProfile->imutProfile;
        }

        // Jika tidak ada, ambil profil yang valid untuk periode laporan
        return $this->profileValidForPeriod(
            $laporanImut->assessment_period_start,
            $laporanImut->assessment_period_end
        );
    }

    public function profileById($profileId): HasOne
    {
        return $this->hasOne(ImutProfile::class)->where('id', $profileId);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get validation rules for unique fields with soft deletes
     *
     * @param int|null $ignoreId
     * @return array
     */
    public function getUniqueValidationRules(?int $ignoreId = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255', $this->uniqueRule('title', $ignoreId)],
        ];
    }
}
