<?php

namespace App\Services\DailyReport;

use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Models\LaporanImutAutoGenerationSetting;
use Carbon\Carbon;
use App\Services\DailyReport\CachedSettingsService;

class DailyReportEntryContextService
{
    public function resolveTemplate(?string $indicatorId = null, int|string|null $recordId = null): ?FormTemplate
    {
        if ($indicatorId) {
            return FormTemplate::with(['formFields.options', 'imutProfile.imutData.categories'])
                ->find((int) $indicatorId);
        }

        if ($recordId) {
            $repo = app(\App\Repositories\Interfaces\DailyReportResponseRepositoryInterface::class);
            $record = $repo->getByIdWithRelations((int)$recordId, ['formTemplate.formFields.options', 'formTemplate.imutProfile.imutData.categories']);

            return $record?->formTemplate ?? null;
        }

        return null;
    }

    public function getBackDataEntryDays(): int
    {
        return CachedSettingsService::getBackDataEntryDays();
    }

    public function getFormattedDate(?string $date = null): string
    {
        $date = $date ?? now()->format('Y-m-d');

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('d F Y');
        } catch (\Exception) {
            return now()->format('d F Y');
        }
    }

    public function getFormTitle(?FormTemplate $formTemplate, string $fallback = 'Laporan Harian'): string
    {
        if ($formTemplate?->imutProfile?->title) {
            return $formTemplate->imutProfile->title;
        }

        return $fallback;
    }

    public function getCategoryBadgeColor(?FormTemplate $formTemplate): string
    {
        if ($formTemplate?->imutProfile?->title) {
            $colors = ['blue', 'green', 'purple', 'orange', 'red', 'indigo', 'pink'];
            $index = abs(crc32($formTemplate->imutProfile->title)) % count($colors);

            return $colors[$index];
        }

        return 'gray';
    }
}