<?php

namespace Tests\Unit\Services;

use App\Settings\KaidoSetting;
use App\Services\Filament\Widgets\ImutCapaianAllUnitWidgetService;
use App\Services\ImutChartSeriesService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class ImutCapaianAllUnitWidgetServiceTest extends TestCase
{
    private ImutCapaianAllUnitWidgetService $service;
    private $chartServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock settings to prevent loading issues
        $this->mockSettings();

        $this->chartServiceMock = Mockery::mock(ImutChartSeriesService::class);
        $this->service = new ImutCapaianAllUnitWidgetService($this->chartServiceMock);
    }

    private function mockSettings()
    {
        // Mock the KaidoSetting to prevent loading issues
        $settingsMock = Mockery::mock(KaidoSetting::class);
        $settingsMock->shouldReceive('getAttribute')->andReturn(true);
        $settingsMock->login_enabled = true;
        $settingsMock->password_reset_enabled = true;
        $settingsMock->sso_enabled = false;

        $this->app->instance(KaidoSetting::class, $settingsMock);
    }

    public function test_can_view_returns_false_when_no_user()
    {
        Auth::shouldReceive('user')->andReturn(null);

        $result = $this->service->canView();

        $this->assertFalse($result);
    }

    public function test_get_categories_returns_collection()
    {
        $this->chartServiceMock
            ->shouldReceive('getCategories')
            ->once()
            ->andReturn(['A', 'B', 'C']);

        $result = $this->service->getCategories();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(3, $result);
    }

    public function test_get_default_colors_returns_array()
    {
        $colors = ['#FF0000', '#00FF00', '#0000FF'];

        $this->chartServiceMock
            ->shouldReceive('getDefaultColors')
            ->once()
            ->andReturn($colors);

        $result = $this->service->getDefaultColors();

        $this->assertEquals($colors, $result);
    }

    public function test_get_form_schema_returns_proper_structure()
    {
        $this->chartServiceMock
            ->shouldReceive('getCategories')
            ->once()
            ->andReturn(collect(['A', 'B']));

        $this->chartServiceMock
            ->shouldReceive('getDefaultColors')
            ->once()
            ->andReturn(['#FF0000', '#00FF00']);

        $result = $this->service->getFormSchema();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('show_dataLabels', $result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertFalse($result['show_dataLabels']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
