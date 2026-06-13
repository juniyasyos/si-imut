<?php

namespace App\Models;

if (!class_exists(\App\Models\ImutDataUnitKerja::class, false)) {
    class_alias(\App\Modules\ImutMaster\Models\ImutDataUnitKerja::class, \App\Models\ImutDataUnitKerja::class);
}