<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_header_id',
        'key',
        'label',
        'type',
        'is_required',
        'options',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
    ];

    public function formHeader(): BelongsTo
    {
        return $this->belongsTo(FormHeader::class);
    }
}
