<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_response_id',
        'form_field_id',
        'field_value',
        'compliance_score',
        'is_valid',
        'validation_message',
    ];

    protected $casts = [
        'field_value' => 'array',
        'is_valid' => 'boolean',
    ];

    public function dailyReportResponse(): BelongsTo
    {
        return $this->belongsTo(DailyReportResponse::class);
    }

    public function formField(): BelongsTo
    {
        return $this->belongsTo(EnhancedFormField::class, 'form_field_id');
    }
}
