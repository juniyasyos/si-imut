<?php

namespace App\Models;

if (!class_exists(\App\Models\EnhancedFormField::class, false)) {
    class_alias(\App\Modules\FormEngine\Models\EnhancedFormField::class, \App\Models\EnhancedFormField::class);
}
