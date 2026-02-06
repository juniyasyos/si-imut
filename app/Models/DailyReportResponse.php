<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class DailyReportResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_template_id',
        'unit_kerja_id',
        'submitted_by',
        'report_date',
        'total_score',
        'compliance_status',
        'auto_calculated',
        'calculation_details',
        'responses',
        'notes',
        'validation_status',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'auto_calculated' => 'boolean',
        'compliance_status' => 'boolean',
        'calculation_details' => 'array',
        'validation_status' => 'string',
        'validated_at' => 'datetime',
    ];

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function fieldResponses(): HasMany
    {
        return $this->hasMany(FieldResponse::class);
    }

    // Query Scopes
    public function scopeForUserUnits(Builder $query, User $user): Builder
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        return $query->whereIn('unit_kerja_id', $unitKerjaIds);
    }
}
