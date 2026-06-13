<?php

namespace App\Services;

if (!class_exists(\App\Services\FormTemplateLoadingService::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\FormTemplateLoadingService::class, \App\Services\FormTemplateLoadingService::class);
}
