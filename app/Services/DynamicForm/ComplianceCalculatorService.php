<?php

namespace App\Services\DynamicForm;

if (!class_exists(\App\Services\DynamicForm\ComplianceCalculatorService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\ComplianceCalculatorService::class, \App\Services\DynamicForm\ComplianceCalculatorService::class);
}
