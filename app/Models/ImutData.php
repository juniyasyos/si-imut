<?php

namespace App\Models;

if (!class_exists(\App\Models\ImutData::class, false)) {
    class_alias(\App\Modules\ImutMaster\Models\ImutData::class, \App\Models\ImutData::class);
}
