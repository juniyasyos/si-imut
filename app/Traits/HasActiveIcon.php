<?php

namespace App\Traits;

if (!trait_exists(\App\Traits\HasActiveIcon::class, false)) {
    class_alias(\App\Kernel\Traits\HasActiveIcon::class, \App\Traits\HasActiveIcon::class);
}
