<?php

namespace App\Traits;

if (!trait_exists(\App\Traits\HasPermissionChecks::class, false)) {
    class_alias(\App\Kernel\Traits\HasPermissionChecks::class, \App\Traits\HasPermissionChecks::class);
}
