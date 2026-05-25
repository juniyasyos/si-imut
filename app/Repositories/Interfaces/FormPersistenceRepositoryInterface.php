<?php

namespace App\Repositories\Interfaces;

use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use App\Models\FormTemplate;
use App\Models\ImutProfile;
use Illuminate\Database\Eloquent\Collection;

interface FormPersistenceRepositoryInterface
{
    public function findLatestTemplateForProfile(ImutProfile $record): ?FormTemplate;

    public function createActiveTemplate(ImutProfile $record, array $data): FormTemplate;

    public function updateTemplate(FormTemplate $formTemplate, array $data): void;

    public function getFields(FormTemplate $formTemplate): Collection;

    public function createField(FormTemplate $formTemplate, array $data): EnhancedFormField;

    public function updateField(EnhancedFormField $field, array $data): void;

    public function deleteField(EnhancedFormField $field): void;

    public function getOptions(EnhancedFormField $field): Collection;

    public function createOption(EnhancedFormField $field, array $data): FormFieldOption;

    public function updateOption(FormFieldOption $option, array $data): void;

    public function deleteOptionsByValues(EnhancedFormField $field, array $optionValues): void;

    public function hasResponsesForTemplate(FormTemplate $template): bool;

    public function countResponsesForTemplate(FormTemplate $template): int;

    public function deleteResponsesForTemplate(FormTemplate $template): void;
}