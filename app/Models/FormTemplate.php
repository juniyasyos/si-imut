<?php

namespace App\Models;

if (!class_exists(\App\Models\FormTemplate::class, false)) {
    class_alias(\App\Modules\FormEngine\Models\FormTemplate::class, \App\Models\FormTemplate::class);
}
