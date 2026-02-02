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
        'data_entry_duration',
        'analysis_duration',
        'recommendation_duration',
        'grace_period',
        'auto_calculate',
        'auto_publish',
        'default_unit_kerjas',
        'reminder_schedule',
        'notification_targets',
        'enable_escalation',
        'analysis_template',
        'recommendation_template',
        'required_fields',
        'require_approval',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'auto_calculate' => 'boolean',
        'auto_publish' => 'boolean',
        'enable_escalation' => 'boolean',
        'require_approval' => 'boolean',
        'default_unit_kerjas' => 'array',
        'reminder_schedule' => 'array',
        'notification_targets' => 'array',
        'required_fields' => 'array',
        'period_start_day' => 'integer',
        'period_end_day' => 'integer',
        'data_entry_duration' => 'integer',
        'analysis_duration' => 'integer',
        'recommendation_duration' => 'integer',
        'grace_period' => 'integer',
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
            'data_entry_duration' => 7,
            'analysis_duration' => 3,
            'recommendation_duration' => 2,
            'grace_period' => 2,
            'auto_calculate' => true,
            'auto_publish' => false,
            'default_unit_kerjas' => [],
            'reminder_schedule' => [3, 1], // 3 days and 1 day before deadline
            'notification_targets' => ['pic', 'supervisor'],
            'enable_escalation' => false,
            'analysis_template' => null,
            'recommendation_template' => null,
            'required_fields' => [],
            'require_approval' => false,
        ];
    }

    /**
     * Calculate total deadline days
     */
    public function getTotalDeadlineDaysAttribute(): int
    {
        return $this->data_entry_duration +
            $this->analysis_duration +
            $this->recommendation_duration;
    }

    /**
     * Calculate deadline with grace period
     */
    public function getDeadlineWithGracePeriodAttribute(): int
    {
        return $this->total_deadline_days + $this->grace_period;
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
