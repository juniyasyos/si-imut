<?php

namespace App\Services\DailyReport;

use App\Models\FormTemplate;
use App\Models\User;
use App\Services\DailyReport\Exports\DailyReportMonitoringExport;
use App\Services\DailyReport\Monitoring\MonitoringTemplateService;
use App\Repositories\DailyReport\FormTemplateRepository;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use App\Support\CacheKey;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Facade/Coordinator Service for Daily Report Monitoring operations
 * 
 * Coordinates between multiple specialized services and repositories.
 * Routes requests to appropriate service layer components.
 * 
 * NEW ARCHITECTURE:
 * - MonitoringTemplateService: Handles business logic for monitoring templates
 * - FormTemplateRepository: Handles data access and caching
 * - FormTemplateQueryBuilder: Encapsulates complex query building
 */
class DailyReportMonitoringService
{
    /**
     * In-memory cache to avoid repeated unit kerja lookups in one request.
     *
     * @var array<int, array<int, int>>
     */
    private array $userUnitKerjaIdsCache = [];

    public function __construct(
        private readonly DailyReportResponseRepositoryInterface $dailyReportRepository,
        private readonly MonitoringTemplateService $monitoringTemplateService,
        private readonly FormTemplateRepository $formTemplateRepository,
    ) {
    }

    /**
     * Load monitoring templates for a specific period
     * Routes to MonitoringTemplateService for business logic
     */
    public function loadMonitoringTemplates(User $user, string $selectedMonth): array
    {
        return $this->monitoringTemplateService->loadTemplatesForPeriod($user, $selectedMonth);
    }

    /**
     * Load monitoring data for a specific period
     * Routes to MonitoringTemplateService for business logic
     */
    public function loadMonitoringForPeriod(User $user, string $month): array
    {
        return $this->monitoringTemplateService->loadMonitoringForPeriod($user, $month);
    }

    /**
     * Get total monitoring templates count
     */
    public function getMonitoringTemplateCount(User $user): int
    {
        return $this->monitoringTemplateService->getTotalMonitoringCount($user);
    }

    /**
     * Check if user has monitoring templates
     */
    public function hasMonitoringTemplates(User $user): bool
    {
        return $this->monitoringTemplateService->hasMonitoringTemplates($user);
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
        } catch (\Exception $e) {
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
    public function exportMonitoring(User $user, int $templateId, string $month): \Symfony\Component\HttpFoundation\BinaryFileResponse
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
        } catch (\Exception $e) {
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
        if (isset($this->userUnitKerjaIdsCache[$userId])) {
            return $this->userUnitKerjaIdsCache[$userId];
        }

        $ids = Cache::remember(
            CacheKey::userHasUnitKerjaIds($userId),
            3600,
            fn() => $this->fetchUserUnitKerjaIds($userId)
        );

        return $this->userUnitKerjaIdsCache[$userId] = $ids;
    }

    /**
     * Fetch unit kerja IDs from database
     */
    private function fetchUserUnitKerjaIds(int $userId): array
    {
        $user = User::query()->with('unitKerjas:id')->find($userId);

        return $user?->unitKerjas?->pluck('id')->all() ?? [];
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
