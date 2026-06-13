<?php

namespace App\Traits;

if (!trait_exists(\App\Traits\HasPercentageColor::class, false)) {
    class_alias(\App\Kernel\Traits\HasPercentageColor::class, \App\Traits\HasPercentageColor::class);
}
