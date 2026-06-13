<?php

namespace App\Traits;

if (!trait_exists(\App\Traits\HasUniqueWithSoftDeletes::class, false)) {
    class_alias(\App\Kernel\Traits\HasUniqueWithSoftDeletes::class, \App\Traits\HasUniqueWithSoftDeletes::class);
}
