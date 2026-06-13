<?php

namespace App\Services\Form;

if (!class_exists(\App\Services\Form\ImutDataFormBuilderService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\ImutDataFormBuilderService::class, \App\Services\Form\ImutDataFormBuilderService::class);
}
