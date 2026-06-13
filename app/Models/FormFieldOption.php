<?php

namespace App\Models;

if (!class_exists(\App\Models\FormFieldOption::class, false)) {
    class_alias(\App\Modules\FormEngine\Models\FormFieldOption::class, \App\Models\FormFieldOption::class);
}
