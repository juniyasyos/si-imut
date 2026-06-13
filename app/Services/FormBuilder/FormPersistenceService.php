<?php

namespace App\Services\FormBuilder;

if (!class_exists(\App\Services\FormBuilder\FormPersistenceService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\FormPersistenceService::class, \App\Services\FormBuilder\FormPersistenceService::class);
}
