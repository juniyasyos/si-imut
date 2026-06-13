<?php

namespace App\Services\FormBuilder;

if (!class_exists(\App\Services\FormBuilder\FormDataService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\FormDataService::class, \App\Services\FormBuilder\FormDataService::class);
}
