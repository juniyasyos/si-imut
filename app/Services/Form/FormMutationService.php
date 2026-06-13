<?php

namespace App\Services\Form;

if (!class_exists(\App\Services\Form\FormMutationService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\FormMutationService::class, \App\Services\Form\FormMutationService::class);
}
