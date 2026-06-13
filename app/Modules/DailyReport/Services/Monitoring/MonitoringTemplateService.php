<?php

namespace App\Modules\DailyReport\Services\Monitoring;

use App\Models\User;
use App\Repositories\DailyReport\FormTemplateRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Monitoring Template Service
 * 
 * Handles all business logic for monitoring templates.
 * Coordinates between repository and data formatting layers.
 */
class MonitoringTemplateService
{
    /**
     * In-memory cache to avoid repeated unit kerja lookups in one request.
     *
     * @var array<int, array<int, int>>
     */
    private array $userUnitKerjaIdsCache = [];

    public function __construct(
        private readonly FormTemplateRepository $formTemplateRepository
    ) {
    }

    /**
     * Load monitoring templates for a specific period
     * 
     * Returns formatted template data with response counts
     * 
     * @param User $user
     * @param string $selectedMonth (format: Y-m)
     * @return array
     */
    public function loadTemplatesForPeriod(User $user, string $selectedMonth): array
    {
        try {
            // Validate month parameter is not empty
            if (empty($selectedMonth)) {
                Log::warning('Empty month parameter provided', ['user_id' => $user->id]);
                return [];
            }
            
            // Parse the month
            $date = Carbon::createFromFormat('Y-m', $selectedMonth);
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

            // Get user's unit kerja IDs
            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

            if (empty($unitKerjaIds)) {
                Log::warning('User has no unit kerja', ['user_id' => $user->id]);
                return [];
            }

            // Fetch templates from repository
            $templates = $this->formTemplateRepository->getMonitoringTemplatesForPeriod(
                $unitKerjaIds,
                $startDate,
                $endDate
            );

            // Format response
            return $this->formatTemplatesResponse($templates, $unitKerjaIds);
        } catch (\Exception $e) {
            Log::error('Error loading monitoring templates', [
                'month' => $selectedMonth,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Load all monitoring data for a period
     * 
     * @param User $user
     * @param string $month (format: Y-m)
     * @return array
     */
    public function loadMonitoringForPeriod(User $user, string $month): array
    {
        try {
            // Parse the month
            $date = Carbon::createFromFormat('Y-m', $month);
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

            // Get user's unit kerja IDs
            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

            if (empty($unitKerjaIds)) {
                return [];
            }

            // Fetch from repository
            $templates = $this->formTemplateRepository->getMonitoringTemplatesForPeriod(
                $unitKerjaIds,
                $startDate,
                $endDate
            );

            // Map to simple array format
            return $templates->map(function ($template) {
                $imutData = $template->imutProfile?->imutData;

                return [
                    'id' => $template->id,
                    'title' => $imutData?->title ?? $template->title,
                    'description' => $template->description,
                    'profile_name' => $template->imutProfile?->title ?? null,
                    'imut_profile_version' => $template->imutProfile?->version ?? null,
                    'category' => $imutData?->categories?->category_name ?? null,
                    'response_count' => $template->response_count ?? 0,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading monitoring data for period', [
                'month' => $month,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get total monitoring templates count
     * 
     * @param User $user
     * @return int
     */
    public function getTotalMonitoringCount(User $user): int
    {
        try {
            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);
            return $this->formTemplateRepository->countMonitoring($unitKerjaIds);
        } catch (\Exception $e) {
            Log::error('Error counting monitoring templates', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Check if user has monitoring templates
     * 
     * @param User $user
     * @return bool
     */
    public function hasMonitoringTemplates(User $user): bool
    {
        return $this->getTotalMonitoringCount($user) > 0;
    }

    /**
     * Format templates response with additional data
     * 
     * @param Collection $templates
     * @param array $unitKerjaIds
     * @return array
     */
    private function formatTemplatesResponse(Collection $templates, array $unitKerjaIds): array
    {
        $firstUnitKerjaId = $unitKerjaIds[0] ?? null;

        return $templates->map(function ($template) use ($firstUnitKerjaId) {
            $imutData = $template->imutProfile?->imutData;

            return [
                'id' => $template->id,
                'imut_profile_id' => $template->imutProfile?->id,
                'unit_kerja_id' => $firstUnitKerjaId,
                'title' => $imutData?->title ?? $template->title,
                'description' => $template->description,
                'profile_name' => $template->imutProfile?->title ?? null,
                'imut_profile_version' => $template->imutProfile?->version ?? null,
                'category' => $imutData?->categories?->category_name ?? null,
                'response_count' => $template->response_count ?? 0,
            ];
        })->toArray();
    }

    /**
     * Get user's unit kerja IDs (from User model)
     * 
     * @param int $userId
     * @return array
     */
    private function getUserUnitKerjaIds(int $userId): array
    {
        if (isset($this->userUnitKerjaIdsCache[$userId])) {
            return $this->userUnitKerjaIdsCache[$userId];
        }

        $user = User::query()->with('unitKerjas:id')->find($userId);

        return $this->userUnitKerjaIdsCache[$userId] = $user?->unitKerjas?->pluck('id')->all() ?? [];
    }
}
