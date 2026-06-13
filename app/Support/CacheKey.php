<?php

namespace App\Support;

if (!class_exists(\App\Support\CacheKey::class, false)) {
    class_alias(\App\Kernel\Support\CacheKey::class, \App\Support\CacheKey::class);
}
