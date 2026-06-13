<?php

namespace App\Modules\FormEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormFieldOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'enhanced_form_field_id',
        'option_text',
        'option_value',
        'compliance_value',
        'is_correct',
        'order_index',
    ];

    public function formField(): BelongsTo
    {
        return $this->belongsTo(EnhancedFormField::class, 'enhanced_form_field_id');
    }
}
