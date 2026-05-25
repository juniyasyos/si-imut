<?php

namespace App\Repositories;

use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use App\Models\FormTemplate;
use App\Models\ImutProfile;
use App\Repositories\Interfaces\FormPersistenceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FormPersistenceRepository implements FormPersistenceRepositoryInterface
{
    public function findLatestTemplateForProfile(ImutProfile $record): ?FormTemplate
    {
        return FormTemplate::where('imut_profile_id', $record->id)
            ->orderByDesc('created_at')
            ->first();
    }

    public function createActiveTemplate(ImutProfile $record, array $data): FormTemplate
    {
        return FormTemplate::create([
            'imut_profile_id' => $record->id,
            'version' => $data['version'] ?? 'v1.0',
            'is_active' => true,
            'valid_from' => $data['valid_from'] ?? now()->toDateString(),
            'created_by_user_id' => $data['created_by_user_id'] ?? auth()->id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'compliance_method' => $data['compliance_method'] ?? 'auto_calculate',
            'auto_fail_on_critical' => $data['auto_fail_on_critical'] ?? false,
        ]);
    }

    public function updateTemplate(FormTemplate $formTemplate, array $data): void
    {
        $formTemplate->update($data);
    }

    public function getFields(FormTemplate $formTemplate): Collection
    {
        return $formTemplate->formFields()->get()->keyBy('field_key');
    }

    public function createField(FormTemplate $formTemplate, array $data): EnhancedFormField
    {
        return EnhancedFormField::create([
            'form_template_id' => $formTemplate->id,
            'field_key' => $data['field_key'],
            'field_label' => $data['field_label'],
            'field_description' => $data['field_description'] ?? null,
            'field_type' => $data['field_type'] ?? 'text',
            'validation_config' => $data['validation_config'] ?? [],
            'history_suggestions' => $data['history_suggestions'] ?? null,
            'compliance_weight' => $data['compliance_weight'] ?? 2,
            'is_critical_field' => $data['is_critical_field'] ?? false,
            'conditional_logic' => $data['conditional_logic'] ?? null,
            'compliance_rules' => $data['compliance_rules'] ?? null,
            'order_index' => $data['order_index'] ?? 1,
            'time_format' => $data['time_format'] ?? 'HM',
            'default_valid_duration' => $data['default_valid_duration'] ?? 480,
        ]);
    }

    public function updateField(EnhancedFormField $field, array $data): void
    {
        $field->update($data);
    }

    public function deleteField(EnhancedFormField $field): void
    {
        $field->delete();
    }

    public function getOptions(EnhancedFormField $field): Collection
    {
        return $field->options()->get()->keyBy('option_value');
    }

    public function createOption(EnhancedFormField $field, array $data): FormFieldOption
    {
        return FormFieldOption::create([
            'enhanced_form_field_id' => $field->id,
            'option_text' => $data['option_text'],
            'option_value' => $data['option_value'],
            'is_correct' => $data['is_correct'] ?? true,
            'compliance_value' => $data['compliance_value'] ?? (($data['is_correct'] ?? false) ? 100 : 0),
            'order_index' => $data['order_index'] ?? 1,
        ]);
    }

    public function updateOption(FormFieldOption $option, array $data): void
    {
        $option->update($data);
    }

    public function deleteOptionsByValues(EnhancedFormField $field, array $optionValues): void
    {
        if (! empty($optionValues)) {
            $field->options()->whereIn('option_value', $optionValues)->delete();
        }
    }

    public function hasResponsesForTemplate(FormTemplate $template): bool
    {
        return $template->dailyReportResponses()->exists();
    }

    public function countResponsesForTemplate(FormTemplate $template): int
    {
        return $template->dailyReportResponses()->count();
    }

    public function deleteResponsesForTemplate(FormTemplate $template): void
    {
        $template->dailyReportResponses()->delete();
    }
}