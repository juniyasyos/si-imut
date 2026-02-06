<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanImutAutoGenerationSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'is_enabled',
        'frequency',
        'period_start_day',
        'period_end_day',
        'report_month_based_on',
        'data_entry_duration',
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
        'period_start_day' => 'integer',
        'period_end_day' => 'integer',
        'data_entry_duration' => 'integer',
        'recommendation_analysis_duration' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate([], static::getDefaults());
    }

    /**
     * Get default configuration
     */
    public static function getDefaults(): array
    {
        return [
            'is_enabled' => false,
            'frequency' => 'monthly',
            'period_start_day' => 5,
            'period_end_day' => 4,
            'report_month_based_on' => 'start',
            'data_entry_duration' => 7,
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
        return $this->data_entry_duration + $this->recommendation_analysis_duration;
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
}
