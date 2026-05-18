<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class LaporanImutAutoGenerationSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'is_enabled',
        'frequency',
        'report_month_based_on',
        'back_data_entry_duration',
        'recommendation_analysis_duration',
        'auto_calculate',
        'auto_publish',
        'default_unit_kerjas',
        'reminder_schedule',
        'notification_targets',
        'enable_escalation',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'auto_calculate' => 'boolean',
        'auto_publish' => 'boolean',
        'enable_escalation' => 'boolean',
        'default_unit_kerjas' => 'array',
        'reminder_schedule' => 'array',
        'notification_targets' => 'array',
        'back_data_entry_duration' => 'integer',
        'recommendation_analysis_duration' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            self::clearCache();
        });

        static::deleted(function ($model) {
            self::clearCache();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the singleton instance with caching
     * Cache for 1 hour or until manually cleared
     */
    public static function getInstance(): self
    {
        return Cache::remember('laporan_imut_auto_generation_setting', 3600, function () {
            return static::firstOrCreate([], static::getDefaults());
        });
    }

    /**
     * Clear cache for singleton instance
     */
    public static function clearCache(): void
    {
        Cache::forget('laporan_imut_auto_generation_setting');
    }

    /**
     * Get default configuration
     */
    public static function getDefaults(): array
    {
        return [
            'is_enabled' => false,
            'frequency' => 'monthly',
            'report_month_based_on' => 'start',
            'back_data_entry_duration' => 6,
            'recommendation_analysis_duration' => 2,
            'auto_calculate' => true,
            'auto_publish' => false,
            'default_unit_kerjas' => [],
            'reminder_schedule' => [3, 1],
            'notification_targets' => ['pic', 'supervisor'],
            'enable_escalation' => false,
        ];
    }

    /**
     * Calculate total deadline days
     */
    public function getTotalDeadlineDaysAttribute(): int
    {
        return $this->back_data_entry_duration + $this->recommendation_analysis_duration;
    }

    /**
     * Check if auto generation is currently active
     */
    public function isActive(): bool
    {
        return $this->is_enabled === true;
    }

    /**
     * Get reminder days as sorted array
     */
    public function getReminderDays(): array
    {
        $days = $this->reminder_schedule ?? [];
        rsort($days); // Sort descending
        return $days;
    }

    /**
     * Check if should send reminder on specific day
     */
    public function shouldSendReminder(int $daysBeforeDeadline): bool
    {
        return in_array($daysBeforeDeadline, $this->getReminderDays());
    }

    /**
     * Get default unit kerja IDs
     */
    public function getDefaultUnitKerjaIds(): array
    {
        return $this->default_unit_kerjas ?? [];
    }

    /**
     * Get back data entry duration (days allowed to input past data)
     */
    public function getBackDataEntryDays(): int
    {
        return $this->back_data_entry_duration ?? 6;
    }

    /**
     * Get period start day (always 1 for full month)
     */
    public function getPeriodStartDay(): int
    {
        return 1;
    }

    /**
     * Get period end day - last day of month
     * For most calculations, you'll pass a specific month/year
     */
    public function getPeriodEndDay($year = null, $month = null): int
    {
        if (!$year || !$month) {
            // Default to current month
            $year = $year ?? date('Y');
            $month = $month ?? date('m');
        }

        return (int)\Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('d');
    }
}
