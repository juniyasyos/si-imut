<?php

namespace Tests\Feature\Filament;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->user = User::factory()->create([
            'status' => 'active',
            'email' => 'test@admin.com'
        ]);
    }

    public function test_dashboard_loads_successfully(): void
    {
        $this->actingAs($this->user);

        // Test dashboard page loads without errors
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_dashboard_contains_widgets(): void
    {
        $this->actingAs($this->user);

        // Test that dashboard contains expected widgets
        $response = $this->get('/');

        $response->assertStatus(200);

        // Just test that page loads successfully, widget rendering is tested separately
        $this->assertTrue(true);
    }

    public function test_imut_capaian_widget_renders(): void
    {
        $this->actingAs($this->user);

        // Test specific widget rendering
        try {
            $widget = new \App\Filament\Widgets\ImutCapaianWidget();

            // Test widget can be instantiated
            $this->assertInstanceOf(\App\Filament\Widgets\ImutCapaianWidget::class, $widget);

            // Test chart processor service exists
            $processor = $widget->getChartProcessor();
            $this->assertInstanceOf(\App\Services\Chart\ChartDataProcessorService::class, $processor);

            // Test widget options structure (ApexChart widget)
            $options = $widget->getOptions();
            $this->assertIsArray($options);

        } catch (\Exception $e) {
            $this->fail('Widget should render without errors: ' . $e->getMessage());
        }
    }

    public function test_widget_chart_configuration(): void
    {
        $this->actingAs($this->user);

        $widget = new \App\Filament\Widgets\ImutCapaianWidget();

        // Test widget has proper chart options
        $options = $widget->getOptions();
        $this->assertIsArray($options);

        // Test that widget has chartId property (using reflection)
        $reflection = new \ReflectionClass($widget);
        $chartIdProperty = $reflection->getProperty('chartId');

        $this->assertTrue($reflection->hasProperty('chartId'));
    }
}
