<?php

use App\Services\Filament\Widgets\DashboardWidgetService;
use App\Services\DashboardImutService;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->dashboardService = Mockery::mock(DashboardImutService::class);
    $this->service = new DashboardWidgetService($this->dashboardService);

    // Mock user
    $this->user = Mockery::mock();
    Auth::shouldReceive('user')->andReturn($this->user);
});

afterEach(function () {
    Mockery::close();
});

describe('DashboardWidgetService', function () {
    it('can check view permission', function () {
        $this->user->shouldReceive('can')
            ->with('widget_DashboardSiimutOverview')
            ->andReturn(true);

        $canView = $this->service->canViewDashboard();

        expect($canView)->toBe(true);
    });

    it('returns no data stat when no indicators', function () {
        $this->dashboardService
            ->shouldReceive('getAllDashboardData')
            ->once()
            ->andReturn(['totalIndikator' => 0]);

        $stats = $this->service->getDashboardStats();

        expect($stats)->toHaveCount(1)
            ->and($stats[0]['label'])->toBe('📢 Belum Ada Laporan Aktif')
            ->and($stats[0]['color'])->toBe('gray');
    });

    it('returns formatted stats when data exists', function () {
        $mockData = [
            'totalIndikator' => 10,
            'chart' => ['test' => [1, 2, 3]]
        ];

        $mockConfig = [
            [
                'label' => 'Test Stat',
                'key' => 'totalIndikator',
                'description' => 'Test Description',
                'color' => 'primary',
                'chart' => 'test'
            ]
        ];

        $this->dashboardService
            ->shouldReceive('getAllDashboardData')
            ->once()
            ->andReturn($mockData);

        $this->dashboardService
            ->shouldReceive('getStatsConfig')
            ->with($mockData)
            ->once()
            ->andReturn($mockConfig);

        $this->dashboardService
            ->shouldReceive('formatValue')
            ->with(10, null)
            ->once()
            ->andReturn('10');

        $stats = $this->service->getDashboardStats();

        expect($stats)->toHaveCount(1)
            ->and($stats[0]['label'])->toBe('Test Stat')
            ->and($stats[0]['value'])->toBe('10')
            ->and($stats[0]['color'])->toBe('primary');
    });
});
