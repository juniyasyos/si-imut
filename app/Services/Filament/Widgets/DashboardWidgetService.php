<?php

namespace App\Services\Filament\Widgets;

use App\Services\DashboardImutService;
use Illuminate\Support\Facades\Auth;

class DashboardWidgetService
{
    public function __construct(
        private DashboardImutService $dashboardService
    ) {}

    /**
     * Check if user can view dashboard widget
     */
    public function canViewDashboard(): bool
    {
        $user = Auth::user();
        return $user && $user->can('widget_DashboardSiimutOverview');
    }

    /**
     * Get dashboard stats data
     */
    public function getDashboardStats(): array
    {
        $data = $this->dashboardService->getAllDashboardData();

        if (($data['totalIndikator'] ?? 0) === 0) {
            return [
                $this->createNoDataStat()
            ];
        }

        return $this->createStatsFromData($data);
    }

    /**
     * Create no data stat configuration
     */
    private function createNoDataStat(): array
    {
        return [
            'label' => '📢 Belum Ada Laporan Aktif',
            'value' => '',
            'description' => 'Tidak dapat menampilkan data karena belum ada laporan aktif.',
            'icon' => 'heroicon-o-exclamation-triangle',
            'color' => 'gray'
        ];
    }

    /**
     * Create stats from dashboard data
     */
    private function createStatsFromData(array $data): array
    {
        $statsConfig = $this->dashboardService->getStatsConfig($data);

        return collect($statsConfig)->map(function ($config) use ($data) {
            return [
                'label' => $config['label'],
                'value' => $this->dashboardService->formatValue(
                    data_get($data, $config['key']),
                    $config['format'] ?? null
                ),
                'icon' => $config['icon'] ?? null,
                'description' => $config['description'],
                'descriptionIcon' => $config['descriptionIcon'] ?? null,
                'chart' => $data['chart'][$config['chart']] ?? [],
                'color' => is_callable($config['color']) ? $config['color']($data) : $config['color']
            ];
        })->toArray();
    }
}
