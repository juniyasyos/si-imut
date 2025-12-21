<?php

namespace App\Models;

use App\Policies\DailyReportEntryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReportEntry extends Model
{
    use HasFactory;

    protected $table = 'daily_report_entries';

    protected $fillable = [
        'form_header_id',
        'form_template_id',
        'unit_kerja_id',
        'submitted_by',
        'report_date',
        'entry_time',
        'responses',
    ];

    protected $casts = [
        'responses' => 'array',
        'report_date' => 'date',
        'entry_time' => 'datetime:H:i',
    ];

    /**
     * Get the policy class for authorization.
     */
    public static function getPolicy(): string
    {
        return DailyReportEntryPolicy::class;
    }

    // Relations
    public function formHeader(): BelongsTo
    {
        return $this->belongsTo(FormHeader::class);
    }

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // Query Scopes
    public function scopeForUserUnits(Builder $query, User $user): Builder
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return $query->whereIn('unit_kerja_id', $unitKerjaIds);
    }

    public function scopeForIndicator(Builder $query, int $formHeaderId): Builder
    {
        return $query->where(function ($q) use ($formHeaderId) {
            $q->where('form_header_id', $formHeaderId)
                ->orWhere('form_template_id', $formHeaderId);
        });
    }

    public function scopeForPeriod(Builder $query, string $period): Builder
    {
        [$year, $month] = explode('-', $period);
        return $query->whereYear('report_date', $year)
            ->whereMonth('report_date', $month);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year);
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('report_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Helper Methods
    public function getFormattedReportDateAttribute(): string
    {
        return $this->report_date->format('d M Y');
    }

    public function getFormattedEntryTimeAttribute(): string
    {
        return $this->entry_time->format('H:i');
    }

    public function getPeriodAttribute(): string
    {
        return $this->report_date->format('Y-m');
    }
}
