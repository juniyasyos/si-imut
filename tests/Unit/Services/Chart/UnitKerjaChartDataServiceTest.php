<?php

namespace Tests\Unit\Services\Chart;

use Tests\TestCase;
use App\Services\Chart\UnitKerjaChartDataService;
use App\Services\Calculator\ImutCalculatorService;
use App\Services\Chart\ChartDataProcessorService;
use App\Models\ImutCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mockery;

class UnitKerjaChartDataServiceTest extends TestCase
{
    private UnitKerjaChartDataService $service;
    private ImutCalculatorService $calculator;
    private ChartDataProcessorService $chartProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new ImutCalculatorService();
        $this->chartProcessor = new ChartDataProcessorService($this->calculator);
        $this->service = new UnitKerjaChartDataService($this->calculator, $this->chartProcessor);
    }

    /** @test */
    public function it_builds_unit_kerja_chart_series_correctly(): void
    {
        // Arrange - Mock laporan data
        $mockLaporans = collect([
            (object) [
                'laporanUnitKerjas' => collect([
                    (object) [
                        'imutPenilaians' => collect([
                            (object) [
                                'numerator_value' => 80,
                                'denominator_value' => 100,
                                'imutProfil' => (object) [
                                    'imutData' => (object) [
                                        'imut_kategori_id' => 1
                                    ]
                                ]
                            ]
                        ])
                    ]
                ])
            ]
        ]);

        // Create controlled categories
        $mockCategories = collect([
            (object) ['id' => 1, 'short_name' => 'CAT1']
        ]);

        // Act
        $result = $this->service->buildUnitKerjaChartSeries($mockLaporans, [], $mockCategories);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('CAT1', $result[0]['name']);
        $this->assertEquals([80.0], $result[0]['data']);
        $this->assertArrayHasKey('color', $result[0]);
    }

    /** @test */
    public function it_handles_empty_penilaians_correctly(): void
    {
        // Arrange - Mock laporan with no penilaians
        $mockLaporans = collect([
            (object) [
                'laporanUnitKerjas' => collect([
                    (object) [
                        'imutPenilaians' => collect([])
                    ]
                ])
            ]
        ]);

        // Create controlled categories
        $mockCategories = collect([
            (object) ['id' => 1, 'short_name' => 'CAT1']
        ]);

        // Act
        $result = $this->service->buildUnitKerjaChartSeries($mockLaporans, [], $mockCategories);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals([0], $result[0]['data']);
    }

    /** @test */
    public function it_applies_custom_colors_from_filter_data(): void
    {
        // Arrange
        $mockLaporans = collect([
            (object) [
                'laporanUnitKerjas' => collect([
                    (object) [
                        'imutPenilaians' => collect([])
                    ]
                ])
            ]
        ]);

        $filterData = [
            'series_colors' => [
                'CAT1' => '#FF0000'
            ]
        ];

        // Create controlled categories
        $mockCategories = collect([
            (object) ['id' => 1, 'short_name' => 'CAT1']
        ]);

        // Act
        $result = $this->service->buildUnitKerjaChartSeries($mockLaporans, $filterData, $mockCategories);

        // Assert
        $this->assertEquals('#FF0000', $result[0]['color']);
    }

    /** @test */
    public function it_uses_default_colors_when_custom_not_provided(): void
    {
        // Arrange
        $mockLaporans = collect([
            (object) [
                'laporanUnitKerjas' => collect([
                    (object) [
                        'imutPenilaians' => collect([])
                    ]
                ])
            ]
        ]);

        // Create controlled categories
        $mockCategories = collect([
            (object) ['id' => 1, 'short_name' => 'CAT1']
        ]);

        // Act
        $result = $this->service->buildUnitKerjaChartSeries($mockLaporans, [], $mockCategories);

        // Assert
        $this->assertEquals('#3B82F6', $result[0]['color']); // First default color
    }

    /** @test */
    public function it_generates_correct_unit_kerja_heading(): void
    {
        // Mock Auth::user() to return a user with unit kerja
        Auth::shouldReceive('user')->andReturn((object) [
            'unitKerjas' => collect([
                (object) ['unit_name' => 'Test Unit']
            ])
        ]);

        // Act
        $heading = $this->service->generateUnitKerjaHeading();

        // Assert
        $this->assertEquals('Capaian IMUT setiap Kategori Untuk Unit Test Unit', $heading);
    }

    /** @test */
    public function it_handles_user_without_unit_kerja(): void
    {
        // Mock Auth::user() to return a user without unit kerja
        Auth::shouldReceive('user')->andReturn((object) [
            'unitKerjas' => collect([])
        ]);

        // Act
        $heading = $this->service->generateUnitKerjaHeading();

        // Assert
        $this->assertEquals('Capaian IMUT Unit Kerja', $heading);
    }

    /** @test */
    public function it_handles_null_user(): void
    {
        // Mock Auth::user() to return null
        Auth::shouldReceive('user')->andReturn(null);

        // Act
        $heading = $this->service->generateUnitKerjaHeading();

        // Assert
        $this->assertEquals('Capaian IMUT Unit Kerja', $heading);
    }
}
