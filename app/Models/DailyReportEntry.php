<?php

namespace App\Models;

use App\Policies\DailyReportEntryPolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReportEntry extends Model
{
    use HasFactory;

    protected $table = 'daily_report_entries';

    protected $fillable = [
        'form_header_id',
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

    public function formHeader(): BelongsTo
    {
        return $this->belongsTo(FormHeader::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
