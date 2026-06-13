<?php

namespace App\Services\Form;

if (!class_exists(\App\Services\Form\FormCalculationService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\FormCalculationService::class, \App\Services\Form\FormCalculationService::class);
}
