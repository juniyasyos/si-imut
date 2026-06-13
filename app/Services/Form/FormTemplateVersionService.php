<?php

namespace App\Services\Form;

if (!class_exists(\App\Services\Form\FormTemplateVersionService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\FormTemplateVersionService::class, \App\Services\Form\FormTemplateVersionService::class);
}
