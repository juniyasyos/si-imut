<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ImutBenchmarking
 *
 * @property int $id
 * @property int $imut_data_id
 * @property int $region_type_id
 * @property string|null $region_name
 * @property float $benchmark_value
 * @property \Illuminate\Support\Carbon $period_start
 * @property \Illuminate\Support\Carbon|null $period_end
 * @property bool $is_active
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $year Computed from period_start
 * @property-read int $month Computed from period_start
 * @property-read \App\Models\ImutData $imutData
 * @property-read \App\Models\RegionType $regionType
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $updater
 *
 * @mixin \Eloquent
 */
class ImutBenchmarking extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imut_data_id',
        'region_type_id',
        'benchmark_value',
        'period_start',
        'period_end',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be appended to model arrays.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'year',
        'month',
    ];

    /**
     * The attributes that should be hidden for arrays and JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'is_active' => 'boolean',
            'benchmark_value' => 'decimal:2',
        ];
    }

    /**
     * Get year from period_start.
     *
     * @return int
     */
    public function getYearAttribute(): int
    {
        return $this->period_start ? $this->period_start->year : now()->year;
    }

    /**
     * Get month from period_start.
     *
     * @return int
     */
    public function getMonthAttribute(): int
    {
        return $this->period_start ? $this->period_start->month : now()->month;
    }

    /**
     * Configure the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope: Filter benchmarking yang aktif untuk periode tertentu
     *
     * @param Builder $query
     * @param \Carbon\Carbon|\Illuminate\Support\Carbon $date
     * @return Builder
     */
    public function scopeActiveForPeriod(Builder $query, \Carbon\Carbon|\Illuminate\Support\Carbon $date): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->where(function ($q2) use ($date) {
                    // Period start <= date
                    $q2->where('period_start', '<=', $date)
                        ->where(function ($q3) use ($date) {
                            // Period end is null (berlaku selamanya) OR period end >= date
                            $q3->whereNull('period_end')
                                ->orWhere('period_end', '>=', $date);
                        });
                })
                // OR period_start is null (legacy data)
                ->orWhereNull('period_start');
            });
    }

    /**
     * Scope: Filter benchmarking by indikator (imut_data_id)
     *
     * @param Builder $query
     * @param int $imutDataId
     * @return Builder
     */
    public function scopeForIndicator(Builder $query, int $imutDataId): Builder
    {
        return $query->where('imut_data_id', $imutDataId);
    }

    /**
     * Scope: Filter benchmarking by region type
     *
     * @param Builder $query
     * @param int|array $regionTypeId
     * @return Builder
     */
    public function scopeForRegion(Builder $query, int|array $regionTypeId): Builder
    {
        if (is_array($regionTypeId)) {
            return $query->whereIn('region_type_id', $regionTypeId);
        }

        return $query->where('region_type_id', $regionTypeId);
    }

    /**
     * Scope: Filter benchmarking by year and optionally month
     * Computed from period_start date
     *
     * @param Builder $query
     * @param int $year
     * @param int|null $month If provided, filter for specific month
     * @return Builder
     */
    public function scopeForYearMonth(Builder $query, int $year, ?int $month = null): Builder
    {
        $query->whereYear('period_start', $year);

        if ($month !== null) {
            $query->whereMonth('period_start', '<=', $month);
        }

        return $query;
    }

    /**
     * Check apakah benchmarking ini valid untuk periode tertentu
     *
     * @param \Carbon\Carbon|\Illuminate\Support\Carbon $date
     * @return bool
     */
    public function isValidForPeriod(\Carbon\Carbon|\Illuminate\Support\Carbon $date): bool
    {
        // Tidak aktif = tidak valid
        if (!$this->is_active) {
            return false;
        }

        // Jika ada period_start dan tanggal sebelum period_start = tidak valid
        if ($this->period_start && $date->lt($this->period_start)) {
            return false;
        }

        // Jika ada period_end dan tanggal setelah period_end = tidak valid
        if ($this->period_end && $date->gt($this->period_end)) {
            return false;
        }

        return true;
    }

    /**
     * Get benchmarking value untuk indikator dan region tertentu pada periode tertentu
     *
     * @param int $imutDataId
     * @param int $regionTypeId
     * @param \Carbon\Carbon|\Illuminate\Support\Carbon $date
     * @return float|null
     */
    public static function getValueForPeriod(
        int $imutDataId,
        int $regionTypeId,
        \Carbon\Carbon|\Illuminate\Support\Carbon $date
    ): ?float {
        $benchmark = static::query()
            ->forIndicator($imutDataId)
            ->forRegion($regionTypeId)
            ->activeForPeriod($date)
            ->orderByDesc('period_start')
            ->first();

        return $benchmark?->benchmark_value;
    }

    /**
     * Get the related ImutProfile.
     */
    public function imutData(): BelongsTo
    {
        return $this->belongsTo(ImutData::class);
    }

    /**
     * Get the related RegionType.
     */
    public function regionType(): BelongsTo
    {
        return $this->belongsTo(RegionType::class);
    }

    /**
     * Get the user who created this benchmarking.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this benchmarking.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
