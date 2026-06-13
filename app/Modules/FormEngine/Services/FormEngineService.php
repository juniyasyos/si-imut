<?php

namespace App\Modules\FormEngine\Services;

use App\Modules\FormEngine\Contracts\FormEngineInterface;
use App\Services\FormTemplateLoadingService;
use Illuminate\Support\Collection;

class FormEngineService implements FormEngineInterface
{
    public function getTemplate(int $templateId)
    {
        return FormTemplateLoadingService::getTemplate($templateId);
    }

    public function getTemplatesByIds(array $templateIds): Collection
    {
        return FormTemplateLoadingService::getTemplatesByIds($templateIds);
    }

    public function getActiveTemplatesForUnitKerjas(array $unitKerjaIds, ?\DateTime $validDate = null): Collection
    {
        return FormTemplateLoadingService::getActiveTemplatesForUnitKerjas($unitKerjaIds, $validDate);
    }

    public function getTemplatesByProfileIds(array $profileIds): Collection
    {
        return FormTemplateLoadingService::getTemplatesByProfileIds($profileIds);
    }
}
