<?php

namespace App\Support;

if (!class_exists(\App\Support\StorageFallback::class, false)) {
    class_alias(\App\Kernel\Support\StorageFallback::class, \App\Support\StorageFallback::class);
}
