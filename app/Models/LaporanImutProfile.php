<?php

namespace App\Models;

if (!class_exists(\App\Models\LaporanImutProfile::class, false)) {
    class_alias(\App\Modules\ImutMaster\Models\LaporanImutProfile::class, \App\Models\LaporanImutProfile::class);
}
