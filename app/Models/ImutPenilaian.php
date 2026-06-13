<?php

namespace App\Models;

if (!class_exists(\App\Models\ImutPenilaian::class, false)) {
    class_alias(\App\Modules\ImutMaster\Models\ImutPenilaian::class, \App\Models\ImutPenilaian::class);
}
