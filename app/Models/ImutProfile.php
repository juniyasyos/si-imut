<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\ImutBenchmarking;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUniqueWithSoftDeletes;

class ImutProfile extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasUniqueWithSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'imut_profil';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'imut_data_id',
        'version',
        'valid_from',
        'valid_until',
        'rationale',
        'quality_dimension',
        'objective',
        'operational_definition',
        'indicator_type',
        'numerator_formula',
        'denominator_formula',
        'inclusion_criteria',
        'exclusion_criteria',
        'data_source',
        'data_collection_frequency',
        'analysis_plan',
        'target_operator',
        'target_value',
        'analysis_period_type',
        'analysis_period_value',
        'data_collection_method',
        'sampling_method',
        'data_collection_tool',
        'responsible_person',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty('version') || empty($model->slug)) {
                $model->slug = $model->generateSlug($model->version);
            }

            // Auto-set valid_from jika belum diset
            if (empty($model->valid_from)) {
                $model->valid_from = now()->toDateString();
            }

            // Skip validation during seeding to allow test data creation
            if (!app()->runningInConsole() || !config('app.disable_imut_validation', false)) {
                // Validasi period overlap
                $model->validatePeriodOverlap();

                // Validasi multiple active profiles
                $model->validateSingleActiveProfile();
            }
        });
    }

    /**
     * Generate a unique slug based on the given string.
     *
     * @param string $source
     * @return string
     */
    public function generateSlug(string $source): string
    {
        $slugBase = Str::slug($source);
        $uuid = Str::uuid()->toString();

        $slug = "{$slugBase}-{$uuid}";

        return $slug;
    }


    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Cek apakah profil valid pada tanggal tertentu
     */
    public function isValidOnDate($date): bool
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;

        // Harus sudah mulai berlaku
        if ($this->valid_from && $checkDate->lt($this->valid_from)) {
            return false;
        }

        // Jika ada tanggal berakhir, harus belum berakhir
        if ($this->valid_until && $checkDate->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah profil valid pada periode tertentu
     */
    public function isValidForPeriod($startDate, $endDate): bool
    {
        $start = is_string($startDate) ? \Carbon\Carbon::parse($startDate) : $startDate;
        $end = is_string($endDate) ? \Carbon\Carbon::parse($endDate) : $endDate;

        // Profil harus berlaku sebelum atau pada akhir periode
        if ($this->valid_from && $this->valid_from->gt($end)) {
            return false;
        }

        // Profil harus belum berakhir sebelum atau pada awal periode
        if ($this->valid_until && $this->valid_until->lt($start)) {
            return false;
        }

        return true;
    }

    /**
     * Validate that this profile doesn't overlap with existing profiles
     * for the same ImutData
     */
    protected function validatePeriodOverlap(): void
    {
        if (!$this->valid_from || !$this->imut_data_id) {
            return;
        }

        $query = static::where('imut_data_id', $this->imut_data_id);

        // Exclude current record if updating
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        $validUntil = $this->valid_until ?: '9999-12-31';

        $overlapping = $query->where(function ($q) use ($validUntil) {
            $q->where(function ($subQ) use ($validUntil) {
                // Case 1: New valid_from falls within existing period
                $subQ->where('valid_from', '<=', $this->valid_from)
                    ->where(function ($innerQ) {
                        $innerQ->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', $this->valid_from);
                    });
            })
                ->orWhere(function ($subQ) use ($validUntil) {
                    // Case 2: New valid_until falls within existing period  
                    $subQ->where('valid_from', '<=', $validUntil)
                        ->where(function ($innerQ) use ($validUntil) {
                            $innerQ->whereNull('valid_until')
                                ->orWhere('valid_until', '>=', $validUntil);
                        });
                })
                ->orWhere(function ($subQ) use ($validUntil) {
                    // Case 3: New period completely encompasses existing period
                    $subQ->where('valid_from', '>=', $this->valid_from)
                        ->where(function ($innerQ) use ($validUntil) {
                            $innerQ->whereNull('valid_until')
                                ->orWhere('valid_until', '<=', $validUntil);
                        });
                });
        })->exists();

        if ($overlapping) {
            throw new \Exception(
                "Period overlap detected for ImutData ID {$this->imut_data_id}. "
                    . "Period {$this->valid_from} to {$validUntil} overlaps with existing profile periods. "
                    . "Each ImutData can only have one active profile per time period."
            );
        }
    }

    /**
     * Validate that only one profile is active at the same time for the same ImutData
     */
    protected function validateSingleActiveProfile(): void
    {
        if (!$this->valid_from || !$this->imut_data_id) {
            return;
        }

        $query = static::where('imut_data_id', $this->imut_data_id);

        // Exclude current record if updating
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        // Check for overlapping validity periods
        $validUntil = $this->valid_until ?: '9999-12-31'; // If no end date, consider it active indefinitely

        $overlapping = $query->where(function ($q) use ($validUntil) {
            $q->where(function ($subQ) use ($validUntil) {
                // Check if any existing profile's validity period overlaps
                $subQ->where('valid_from', '<=', $validUntil)
                    ->where(function ($innerQ) {
                        $innerQ->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', $this->valid_from);
                    });
            });
        })->exists();

        if ($overlapping) {
            throw new \Exception(
                "Multiple active profiles detected for ImutData ID {$this->imut_data_id}. "
                    . "Only one profile can be active at any given time. Please set valid_until date on existing active profiles before creating a new one."
            );
        }
    }

    /**
     * Get the currently active profile for a given ImutData on a specific date
     */
    public static function getActiveProfile(int $imutDataId, $date = null): ?static
    {
        $checkDate = $date ?: now()->toDateString();

        return static::where('imut_data_id', $imutDataId)
            ->validOnDate($checkDate)
            ->orderBy('valid_from', 'desc')
            ->first();
    }

    /**
     * Scope to get only active profiles (no valid_until or future valid_until)
     */
    public function scopeActive($query, $date = null)
    {
        $checkDate = $date ?: now()->toDateString();
        return $this->scopeValidOnDate($query, $checkDate);
    }

    /**
     * Scope untuk profil yang valid pada tanggal tertentu
     */
    public function scopeValidOnDate($query, $date)
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date)->toDateString() : $date->toDateString();

        return $query->where(function ($q) use ($checkDate) {
            $q->whereNull('valid_from')
                ->orWhere('valid_from', '<=', $checkDate);
        })
            ->where(function ($q) use ($checkDate) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $checkDate);
            });
    }
    /**
     * Scope untuk profil yang valid pada periode tertentu
     */
    public function scopeValidForPeriod($query, $startDate, $endDate)
    {
        $start = is_string($startDate) ? \Carbon\Carbon::parse($startDate)->startOfDay() : $startDate;
        $end = is_string($endDate) ? \Carbon\Carbon::parse($endDate)->endOfDay() : $endDate;

        return $query->where(function ($q) use ($end) {
            // Profil harus sudah mulai berlaku sebelum atau pada akhir periode
            $q->whereNull('valid_from')
                ->orWhere('valid_from', '<=', $end->toDateString());
        })
            ->where(function ($q) use ($start) {
                // Profil harus belum berakhir setelah atau pada awal periode
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $start->toDateString());
            });
    }

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
            'target_value' => 'integer',
            'analysis_period_value' => 'integer',
        ];
    }

    /**
     * Get the options for activity log.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('imut_profile')
            ->setDescriptionForEvent(fn(string $eventName) => "Profil IMUT telah {$eventName}");
    }

    /**
     * Scope a query to only include profil with a certain indicator type.
     */
    public function scopeOfIndicatorType($query, string $type)
    {
        return $query->where('indicator_type', $type);
    }

    /**
     * Accessor for full indicator type label.
     */
    public function getIndicatorTypeLabelAttribute(): string
    {
        return match ($this->indicator_type) {
            'process' => 'Proses',
            'output' => 'Hasil (Output)',
            'outcome' => 'Dampak (Outcome)',
            default => 'Tidak diketahui',
        };
    }

    /**
     * Get the related ImutData.
     */
    public function imutData()
    {
        return $this->belongsTo(ImutData::class);
    }

    /**
     * Get the form templates for this profile.
     */
    public function formTemplates()
    {
        return $this->hasMany(FormTemplate::class);
    }

    public function penilaian()
    {
        return $this->hasMany(ImutPenilaian::class, 'imut_profil_id');
    }

    public function penilaianFiltered($laporanId)
    {
        return $this->hasMany(ImutPenilaian::class, 'imut_profil_id')
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId))
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value');
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
            'slug' => ['nullable', 'string', 'max:255', $this->uniqueRule('slug', $ignoreId)],
        ];
    }
    /**
     * Find all overlapping profiles for cleanup purposes
     */
    public static function findOverlappingProfiles(): array
    {
        $overlapping = [];
        $allProfiles = static::orderBy('imut_data_id', 'asc')->orderBy('valid_from', 'asc')->get();

        foreach ($allProfiles as $profile) {
            if (!$profile->valid_from) continue;

            $validUntil = $profile->valid_until ?: '9999-12-31';

            $conflicts = static::where('imut_data_id', $profile->imut_data_id)
                ->where('id', '!=', $profile->id)
                ->where(function ($q) use ($profile, $validUntil) {
                    $q->where(function ($subQ) use ($profile) {
                        $subQ->where('valid_from', '<=', $profile->valid_from)
                            ->where(function ($innerQ) use ($profile) {
                                $innerQ->whereNull('valid_until')
                                    ->orWhere('valid_until', '>=', $profile->valid_from);
                            });
                    });
                })->get();

            if ($conflicts->isNotEmpty()) {
                $overlapping[] = [
                    'profile_id' => $profile->id,
                    'imut_data_id' => $profile->imut_data_id,
                    'conflicts_with' => $conflicts->pluck('id')->toArray()
                ];
            }
        }

        return $overlapping;
    }

    /**
     * Get statistics about profile overlaps
     */
    public static function getOverlapStatistics(): array
    {
        $total = static::count();
        $totalImutData = static::distinct('imut_data_id')->count('imut_data_id');
        $overlapping = static::findOverlappingProfiles();

        return [
            'total_profiles' => $total,
            'total_imut_data' => $totalImutData,
            'avg_profiles_per_imut_data' => $totalImutData > 0 ? round($total / $totalImutData, 2) : 0,
            'overlapping_profiles_count' => count($overlapping),
        ];
    }
}
