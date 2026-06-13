<?php

namespace App\Traits;

if (!trait_exists(\App\Traits\HasTableHelpers::class, false)) {
    class_alias(\App\Kernel\Traits\HasTableHelpers::class, \App\Traits\HasTableHelpers::class);
}
