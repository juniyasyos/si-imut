<?php

namespace App\Traits;

if (!trait_exists(\App\Traits\ImutInitializer::class, false)) {
    class_alias(\App\Kernel\Traits\ImutInitializer::class, \App\Traits\ImutInitializer::class);
}