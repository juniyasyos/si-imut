<?php

namespace App\Models;

if (!class_exists(\App\Models\FieldResponse::class, false)) {
    class_alias(\App\Modules\FormEngine\Models\FieldResponse::class, \App\Models\FieldResponse::class);
}
