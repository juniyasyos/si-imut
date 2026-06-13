<?php

namespace App\Modules\FormEngine\Contracts;

use Illuminate\Support\Collection;

interface FormEngineInterface
{
    /**
     * Get single template by ID
     */
    public function getTemplate(int $templateId);

    /**
     * Get multiple templates by IDs
     */
    public function getTemplatesByIds(array $templateIds): Collection;

    /**
     * Get active templates for unit kerjas
     */
    public function getActiveTemplatesForUnitKerjas(array $unitKerjaIds, ?\DateTime $validDate = null): Collection;

    /**
     * Get templates by profile IDs
     */
    public function getTemplatesByProfileIds(array $profileIds): Collection;
}
