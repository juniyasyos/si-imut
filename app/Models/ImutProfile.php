<?php

namespace App\Models;

if (!class_exists(\App\Models\ImutProfile::class, false)) {
    class_alias(\App\Modules\ImutMaster\Models\ImutProfile::class, \App\Models\ImutProfile::class);
}
