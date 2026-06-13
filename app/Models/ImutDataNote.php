<?php

namespace App\Models;

if (!class_exists(\App\Models\ImutDataNote::class, false)) {
    class_alias(\App\Modules\ImutMaster\Models\ImutDataNote::class, \App\Models\ImutDataNote::class);
}
