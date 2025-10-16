<?php

namespace App\Domains\Reporting\Models;

use App\Domains\Imut\Models\ImutPenilaian;
use App\Domains\Organization\Models\UnitKerja;
use App\Models\User;
use App\Support\CacheKey;
use Database\Factories\LaporanImutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasUniqueWithSoftDeletes;

/**
 * Class LaporanImut
 *
 * @property int $id
 * @property string $name
 * @property string $status
 * @property Carbon $assessment_period_start
 * @property Carbon $assessment_period_end
 * @property int $created_by
 * @property-read User $createdBy
 * @property-read \Illuminate\Support\Collection|UnitKerja[] $unitKerjas
 * @property-read \Illuminate\Support\Collection|LaporanUnitKerja[] $laporanUnitKerjas
 * @property-read \Illuminate\Support\Collection|ImutPenilaian[] $imutPenilaians
 */
class LaporanImut extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, HasUniqueWithSoftDeletes;

    /** @var string Status sedang berlangsung */
    public const STATUS_PROCESS = 'process';

    /** @var string Status selesai */
    public const STATUS_COMPLETE = 'complete';

    /** @var string Status dibatalkan */
    public const STATUS_COMINGSOON = 'coming_soon';

    /**
     * Mass assignable attributes
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
        'assessment_period_start',
        'assessment_period_end',
        'report_month',
        'report_year',
        'created_by',
    ];

    /**
     * Guarded attributes
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Hidden attributes in serialization
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Attribute casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deleted_at' => 'datetime',
        'assessment_period_start' => 'date',
        'assessment_period_end' => 'date',
        'report_month' => 'integer',
        'report_year' => 'integer',
    ];

    /**
     * Boot model events
     */
    protected static function booted(): void
    {
        static::creating(function (self $laporan): void {
            if (empty($laporan->slug)) {
                // Buat slug dengan periode untuk memastikan unik berdasarkan bulan-tahun
                $baseSlug = Str::slug($laporan->name ?? 'laporan-imut');
                $periodSlug = ($laporan->report_year ?? now()->year) . '-' .
                             str_pad($laporan->report_month ?? now()->month, 2, '0', STR_PAD_LEFT);
                $laporan->slug = $baseSlug . '-' . $periodSlug;
            }

            // Auto-fill report_month dan report_year dari assessment_period_start
            if ($laporan->assessment_period_start) {
                $date = Carbon::parse($laporan->assessment_period_start);
                if (empty($laporan->report_month)) {
                    $laporan->report_month = $date->month;
                }
                if (empty($laporan->report_year)) {
                    $laporan->report_year = $date->year;
                }
            }

            // Validate unique period before creating
            static::validateUniquePeriod($laporan);
        });

        static::updating(function (self $laporan): void {
            // Auto-update report_month dan report_year jika assessment_period_start berubah
            if ($laporan->isDirty('assessment_period_start') && $laporan->assessment_period_start) {
                $date = Carbon::parse($laporan->assessment_period_start);
                $laporan->report_month = $date->month;
                $laporan->report_year = $date->year;
            }

            // Validate unique period before updating if period fields changed
            if ($laporan->isDirty(['report_month', 'report_year'])) {
                static::validateUniquePeriod($laporan);
            }
        });

        static::saved(fn(self $laporan) => $laporan->clearCache());
        static::deleted(fn(self $laporan) => $laporan->clearCache());
    }

    /**
     * Clear related cache keys
     */
    public function clearCache(): void
    {
        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
        Cache::forget(CacheKey::dashboardSiimutChartData());
        Cache::forget(CacheKey::dashboardSiimutAllData($this->id));
        Cache::forget(CacheKey::recentLaporanList());

        Cache::forget(CacheKey::getPenilaianStats($this->id, false));
        Cache::forget(CacheKey::getPenilaianStats($this->id, true));
    }

    /**
     * Setup for activity log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relasi ke unit kerja
     */
    public function unitKerjas(): BelongsToMany
    {
        return $this->belongsToMany(UnitKerja::class, 'laporan_unit_kerjas', 'laporan_imut_id', 'unit_kerja_id')
            ->withTimestamps();
    }

    /**
     * Relasi ke penilaian melalui laporan unit kerja
     */
    public function imutPenilaians(): HasManyThrough
    {
        return $this->hasManyThrough(
            ImutPenilaian::class,
            LaporanUnitKerja::class,
            'laporan_imut_id',
            'laporan_unit_kerja_id',
            'id',
            'id'
        );
    }

    /**
     * Relasi ke user pembuat laporan
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke laporan unit kerja
     */
    public function laporanUnitKerjas(): HasMany
    {
        return $this->hasMany(LaporanUnitKerja::class);
    }

    /**
     * Accessor otomatis update status menjadi sesuai dengan kasus real
     */
    public function getStatusAttribute(string $value): string
    {
        $today = now()->startOfDay();
        $start = $this->assessment_period_start->startOfDay();
        $end = $this->assessment_period_end->startOfDay();

        $newStatus = match (true) {
            $today->lt($start) => self::STATUS_COMINGSOON,
            $today->lte($end) => self::STATUS_PROCESS,
            default => self::STATUS_COMPLETE,
        };

        if ($value !== $newStatus) {
            $this->updateQuietly(['status' => $newStatus]);
        }

        return $newStatus;
    }

    /**
     * Mendapatkan nama periode yang jelas berdasarkan bulan dan tahun
     */
    public function getPeriodNameAttribute(): string
    {
        if ($this->report_month && $this->report_year) {
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            return $monthNames[$this->report_month] . ' ' . $this->report_year;
        }

        // Fallback ke format lama
        return $this->assessment_period_start->translatedFormat('F Y');
    }

    /**
     * Scope untuk filter berdasarkan periode tertentu
     */
    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('report_year', $year)->where('report_month', $month);
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('report_year', $year);
    }

    /**
     * Get validation rules for unique fields with soft deletes
     * Note: LaporanImut now uses period-based uniqueness instead of name
     *
     * @param int|null $ignoreId
     * @return array
     */
    public function getUniqueValidationRules(?int $ignoreId = null): array
    {
        return [
            'slug' => ['required', 'string', 'max:255', $this->uniqueRule('slug', $ignoreId)],
            // Note: name is no longer unique due to migration 2025_09_22_135922
            // Period uniqueness is handled by database constraint on [report_year, report_month]
        ];
    }

    /**
     * Validate unique period combination
     * 
     * @param LaporanImut $laporan
     * @throws \Illuminate\Validation\ValidationException
     */
    protected static function validateUniquePeriod(self $laporan): void
    {
        $existingQuery = static::where('report_month', $laporan->report_month)
            ->where('report_year', $laporan->report_year);

        // Exclude current record if updating
        if ($laporan->exists) {
            $existingQuery->where('id', '!=', $laporan->id);
        }

        $existingReport = $existingQuery->first();

        if ($existingReport) {
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $monthName = $monthNames[$laporan->report_month] ?? $laporan->report_month;
            $message = "Laporan untuk periode {$monthName} {$laporan->report_year} sudah ada dengan nama: \"{$existingReport->name}\"";

            throw \Illuminate\Validation\ValidationException::withMessages([
                'report_month' => [$message],
                'report_year' => [$message],
            ]);
        }
    }

    protected static function newFactory(): LaporanImutFactory
    {
        return LaporanImutFactory::new();
    }
}
