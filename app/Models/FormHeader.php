<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'imutdata_id',
        'title',
        'description',
    ];

    public function imutdata(): BelongsTo
    {
        return $this->belongsTo(Imutdata::class, 'imutdata_id');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }
}
