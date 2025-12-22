<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReportResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_template_id',
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
    ];

    protected $casts = [
        'report_date' => 'date',
        'auto_calculated' => 'boolean',
        'compliance_status' => 'boolean',
        'calculation_details' => 'array',
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
        return $this->belongsTo(User::class);
    }

    public function fieldResponses(): HasMany
    {
        return $this->hasMany(FieldResponse::class);
    }
}
