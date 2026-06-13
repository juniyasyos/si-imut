<?php

namespace App\Services\DynamicForm;

if (!class_exists(\App\Services\DynamicForm\DynamicFormService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\DynamicFormService::class, \App\Services\DynamicForm\DynamicFormService::class);
}
