<?php

namespace App\Models;

if (!class_exists(\App\Models\ImutCategory::class, false)) {
    class_alias(\App\Modules\ImutMaster\Models\ImutCategory::class, \App\Models\ImutCategory::class);
}
