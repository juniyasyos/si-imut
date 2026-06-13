<?php

namespace App\Models;

if (!class_exists(\App\Models\FormField::class, false)) {
    class_alias(\App\Modules\FormEngine\Models\FormField::class, \App\Models\FormField::class);
}
