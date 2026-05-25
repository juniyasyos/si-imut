<?php

namespace App\Services\DailyReport;

use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\FormTemplate;
use App\Models\User;
use App\Services\DailyReport\Exports\DailyReportMonitoringExport;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use App\Support\CacheKey;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Service for Daily Report Monitoring operations
 * Handles all business logic for monitoring templates, responses, and exports
 */
class DailyReportMonitoringService
{
    public function __construct(
        private readonly DailyReportResponseRepositoryInterface $dailyReportRepository,
    ) {
    }

    /**
     * Load monitoring templates for a specific period
     */
    public function loadMonitoringTemplates(User $user, string $selectedMonth): array
    {
        try {
            $date = Carbon::createFromFormat('Y-m', $selectedMonth);
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

            $templates = $this->getMonitoringTemplatesQuery($user, $unitKerjaIds)
                ->withCount(['dailyReportResponses as response_count' => function ($query) use ($startDate, $endDate, $unitKerjaIds) {
                    $query->whereBetween('report_date', [$startDate, $endDate]);
                    if (!empty($unitKerjaIds)) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    }
                }])
                ->get();

            $firstUnitKerjaId = $unitKerjaIds[0] ?? null;

            return $templates->map(function ($template) use ($firstUnitKerjaId) {
                return $this->formatTemplateResponse($template, $firstUnitKerjaId);
            })->toArray();
        } catch (Exception $e) {
            Log::error('Error loading monitoring templates', [
                'month' => $selectedMonth,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Load monitoring data for a specific period
     */
    public function loadMonitoringForPeriod(User $user, string $month): array
    {
        try {
            $date = Carbon::createFromFormat('Y-m', $month);
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

            $templates = $this->getMonitoringTemplatesQuery($user, $unitKerjaIds)
                ->withCount(['dailyReportResponses as response_count' => function ($query) use ($startDate, $endDate, $unitKerjaIds) {
                    $query->whereBetween('report_date', [$startDate, $endDate]);
                    if (!empty($unitKerjaIds)) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    }
                }])
                ->get();

            return $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'title' => $template->imutProfile->imutData->title,
                    'description' => $template->description,
                    'profile_name' => $template->imutProfile?->title ?? null,
                    'imut_profile_version' => $template->imutProfile?->version ?? null,
                    'category' => null,
                    'response_count' => $template->response_count ?? 0,
                ];
            })->toArray();
        } catch (Exception $e) {
            Log::error('Error loading monitoring data for period', [
                'month' => $month,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get report count for indicator on specific date
     */
    public function getReportCountForIndicatorDate(int $indicatorId, string $date): int
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return 0;
            }

            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

            return $this->dailyReportRepository->countReportsForIndicatorDate(
                $indicatorId,
                $date,
                $unitKerjaIds
            );
        } catch (Exception $e) {
            Log::error('Error fetching report count', [
                'indicator_id' => $indicatorId,
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Export monitoring data to Excel
     */
    public function exportMonitoring(User $user, int $templateId, string $month): BinaryFileResponse
    {
        try {
            $date = Carbon::createFromFormat('Y-m', $month);
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

            $template = FormTemplate::with([
                'imutProfile.imutData',
                'formFields.options',
                'dailyReportResponses' => function ($query) use ($startDate, $endDate, $unitKerjaIds) {
                    $query->whereBetween('report_date', [$startDate, $endDate])
                        ->with(['submittedBy', 'validator', 'unitKerja', 'fieldResponses.formField']);
                    
                    if (!empty($unitKerjaIds)) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    }
                }
            ])->findOrFail($templateId);

            $filename = $this->generateExportFilename($template, $month);

            return Excel::download(
                new DailyReportMonitoringExport($template),
                $filename
            );
        } catch (Exception $e) {
            Log::error('Export monitoring data failed', [
                'template_id' => $templateId,
                'month' => $month,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get user's unit kerja IDs with caching
     */
    public function getUserUnitKerjaIds(int $userId): array
    {
        return Cache::remember(
            CacheKey::userHasUnitKerjaIds($userId),
            3600,
            fn() => $this->fetchUserUnitKerjaIds($userId)
        );
    }

    /**
     * Fetch unit kerja IDs from database
     */
    private function fetchUserUnitKerjaIds(int $userId): array
    {
        $user = User::find($userId);
        return $user?->unitKerjas()?->pluck('unit_kerja.id')->toArray() ?? [];
    }

    /**
     * Get base query for monitoring templates
     */
    private function getMonitoringTemplatesQuery(User $user, array $unitKerjaIds)
    {
        return FormTemplate::query()
            ->forUserUnits($user)
            ->where('is_active', true)
            ->with(['imutProfile.imutData.categories'])
            ->whereHas('imutProfile', function ($query) {
                $query->where('valid_from', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', now());
                    });
            })
            ->whereHas('imutProfile.imutData', function ($query) {
                $query->where('is_monthly', true);
            });
    }

    /**
     * Format template response data
     */
    private function formatTemplateResponse($template, ?int $firstUnitKerjaId = null): array
    {
        return [
            'id' => $template->id,
            'imut_profile_id' => $template->imutProfile?->id,
            'unit_kerja_id' => $firstUnitKerjaId,
            'title' => $template->imutProfile->imutData->title,
            'description' => $template->description,
            'profile_name' => $template->imutProfile?->title ?? null,
            'imut_profile_version' => $template->imutProfile?->version ?? null,
            'category' => null,
            'response_count' => $template->response_count ?? 0,
        ];
    }

    /**
     * Generate export filename
     */
    private function generateExportFilename(FormTemplate $template, string $month): string
    {
        $title = $template->imutProfile->imutData->title;
        $filename = "monitoring_{$title}_{$month}.xlsx";
        return preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);
    }
}
