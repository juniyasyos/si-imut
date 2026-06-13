<?php

namespace App\Support;

if (!class_exists(\App\Support\ApexChartConfig::class, false)) {
    class_alias(\App\Kernel\Support\ApexChartConfig::class, \App\Support\ApexChartConfig::class);
}
