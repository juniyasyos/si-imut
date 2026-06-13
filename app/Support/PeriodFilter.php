<?php

namespace App\Support;

if (!class_exists(\App\Support\PeriodFilter::class, false)) {
    class_alias(\App\Kernel\Support\PeriodFilter::class, \App\Support\PeriodFilter::class);
}
