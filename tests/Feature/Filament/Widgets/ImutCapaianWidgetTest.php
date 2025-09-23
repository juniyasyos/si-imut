<?php

namespace Tests\Feature\Filament\Widgets;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImutCapaianWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_can_be_instantiated(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user);

        // Test if widget class can be instantiated without errors
        $widget = new \App\Filament\Widgets\ImutCapaianWidget();

        $this->assertInstanceOf(\App\Filament\Widgets\ImutCapaianWidget::class, $widget);
    }

    public function test_widget_chart_processor_service_works(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $widget = new \App\Filament\Widgets\ImutCapaianWidget();

        // Test if chart processor service can be accessed
        $processor = $widget->getChartProcessor();

        $this->assertInstanceOf(\App\Services\Chart\ChartDataProcessorService::class, $processor);
    }

    public function test_widget_can_handle_empty_data(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $widget = new \App\Filament\Widgets\ImutCapaianWidget();

        // Test widget with empty data - should not throw exception
        try {
            $options = $widget->getOptions();
            $this->assertIsArray($options);
        } catch (\Exception $e) {
            $this->fail('Widget should handle empty data gracefully: ' . $e->getMessage());
        }
    }
}
