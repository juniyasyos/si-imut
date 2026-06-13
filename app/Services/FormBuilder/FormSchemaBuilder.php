<?php

namespace App\Services\FormBuilder;

if (!class_exists(\App\Services\FormBuilder\FormSchemaBuilder::class, false)) {
    class_alias(\App\Modules\FormEngine\Services\FormSchemaBuilder::class, \App\Services\FormBuilder\FormSchemaBuilder::class);
}
