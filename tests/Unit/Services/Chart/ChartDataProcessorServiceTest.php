<?php

namespace Tests\Unit\Services\Chart;

use App\Services\Core\ImutCalculatorService;
use App\Services\Chart\ChartDataProcessorService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Mockery;

class ChartDataProcessorServiceTest extends TestCase
{
    private ChartDataProcessorService $processor;
    private $mockCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCalculator = Mockery::mock(ImutCalculatorService::class);
        $this->processor = new ChartDataProcessorService($this->mockCalculator);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_processes_capaian_data_correctly()
    {
        // Mock data structure
        $categories = ['KAT1', 'KAT2'];
        $laporans = collect([
            $this->createMockLaporan(),
            $this->createMockLaporan(),
        ]);

        // Mock calculator responses
        $this->mockCalculator
            ->shouldReceive('evaluatePenilaian')
            ->andReturn(['is_achieved' => true]);

        $result = $this->processor->processCapaianData($laporans, $categories);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('KAT1', $result);
        $this->assertArrayHasKey('KAT2', $result);
        $this->assertCount(2, $result['KAT1']); // 2 laporan
        $this->assertCount(2, $result['KAT2']); // 2 laporan
        $this->assertEquals([100.0, 100.0], $result['KAT1']);
        $this->assertEquals([0.0, 0.0], $result['KAT2']);
    }

    /** @test */
    public function it_processes_category_achievement_data_for_single_report()
    {
        $categories = ['KAT1', 'KAT2'];
        $laporan = $this->createMockLaporan();

        $this->mockCalculator
            ->shouldReceive('evaluatePenilaian')
            ->once()
            ->andReturn(['is_achieved' => true]);

        $result = $this->processor->processCategoryAchievementData($laporan, $categories);

        $this->assertEquals([100.0, 0.0], $result);
    }

    /** @test */
    public function it_builds_chart_series_correctly()
    {
        $processedData = [
            'KAT1' => [10, 15, 20],
            'KAT2' => [5, 8, 12],
        ];

        $formData = [
            'series_types' => ['KAT1' => 'column', 'KAT2' => 'line'],
            'series_colors' => ['KAT1' => '#ff0000', 'KAT2' => '#00ff00'],
        ];

        $colors = ['#000000', '#ffffff'];

        $series = $this->processor->buildChartSeries($processedData, $formData, $colors);

        $this->assertCount(2, $series);

        // Check first series
        $this->assertEquals('KAT1', $series[0]['name']);
        $this->assertEquals('column', $series[0]['type']);
        $this->assertEquals([10, 15, 20], $series[0]['data']);
        $this->assertEquals('#ff0000', $series[0]['color']);

        // Check second series
        $this->assertEquals('KAT2', $series[1]['name']);
        $this->assertEquals('line', $series[1]['type']);
        $this->assertEquals([5, 8, 12], $series[1]['data']);
        $this->assertEquals('#00ff00', $series[1]['color']);
    }

    /** @test */
    public function it_uses_default_values_when_form_data_missing()
    {
        $processedData = ['KAT1' => [10, 15]];
        $formData = []; // Empty form data
        $colors = ['#default'];

        $series = $this->processor->buildChartSeries($processedData, $formData, $colors);

        $this->assertEquals('column', $series[0]['type']); // default type
        $this->assertEquals('#default', $series[0]['color']); // default color
    }

    /** @test */
    public function it_generates_time_labels_correctly()
    {
        $laporans = collect([
            $this->createMockLaporanWithDates('2024-01-01', '2024-01-31'),
            $this->createMockLaporanWithDates('2024-02-01', '2024-03-15'),
        ]);

        $labels = $this->processor->generateTimeLabels($laporans);

        $this->assertCount(2, $labels);
        $this->assertIsString($labels[0]);
        $this->assertIsString($labels[1]);
    }

    /** @test */
    public function it_handles_invalid_dates_in_labels()
    {
        $laporans = collect([
            $this->createMockLaporanWithDates(null, null),
        ]);

        $labels = $this->processor->generateTimeLabels($laporans);

        $this->assertEquals(['Tidak diketahui'], $labels);
    }

    /** @test */
    public function it_processes_unit_kerja_chart_data()
    {
        $penilaianData = collect([
            (object) [
                'report_month' => 1,
                'report_year' => 2024,
                'periode' => '2024-01',
                'total_num' => 80,
                'total_denum' => 100,
                'target' => 75.5,
            ],
            (object) [
                'report_month' => 2,
                'report_year' => 2024,
                'periode' => '2024-02',
                'total_num' => 90,
                'total_denum' => 100,
                'target' => 80.0,
            ],
        ]);

        $this->mockCalculator
            ->shouldReceive('calculatePercentage')
            ->with(80, 100)
            ->andReturn(80.0);

        $this->mockCalculator
            ->shouldReceive('calculatePercentage')
            ->with(90, 100)
            ->andReturn(90.0);

        $result = $this->processor->processUnitKerjaChartData($penilaianData, []);

        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('nilai', $result);
        $this->assertArrayHasKey('target', $result);

        $this->assertCount(2, $result['labels']);
        $this->assertEquals(80.0, $result['nilai']['2024-01']);
        $this->assertEquals(90.0, $result['nilai']['2024-02']);
        $this->assertEquals(75.5, $result['target']['2024-01']);
        $this->assertEquals(80.0, $result['target']['2024-02']);
    }

    /** @test */
    public function it_builds_unit_kerja_chart_series()
    {
        $chartData = [
            'labels' => ['2024-01', '2024-02'],
            'nilai' => ['2024-01' => 80.0, '2024-02' => 90.0],
            'target' => ['2024-01' => 75.0, '2024-02' => 85.0],
        ];

        $config = [
            'nilai_type' => 'column',
            'target_type' => 'line',
            'color_nilai' => '#blue',
            'color_target' => '#red',
        ];

        $series = $this->processor->buildUnitKerjaChartSeries($chartData, $config);

        $this->assertCount(2, $series);

        // Check nilai series
        $this->assertEquals('Nilai IMUT', $series[0]['name']);
        $this->assertEquals('column', $series[0]['type']);
        $this->assertEquals([80.0, 90.0], $series[0]['data']);
        $this->assertEquals('#blue', $series[0]['color']);

        // Check target series
        $this->assertEquals('Target Standar', $series[1]['name']);
        $this->assertEquals('line', $series[1]['type']);
        $this->assertEquals([75.0, 85.0], $series[1]['data']);
        $this->assertEquals('#red', $series[1]['color']);
    }

    /** @test */
    public function it_uses_default_config_for_unit_kerja_series()
    {
        $chartData = [
            'labels' => ['2024-01'],
            'nilai' => ['2024-01' => 80.0],
            'target' => ['2024-01' => 75.0],
        ];

        $series = $this->processor->buildUnitKerjaChartSeries($chartData, []);

        // Check default values
        $this->assertEquals('column', $series[0]['type']);
        $this->assertEquals('line', $series[1]['type']);
        $this->assertEquals('#3b82f6', $series[0]['color']);
        $this->assertEquals('#f59e0b', $series[1]['color']);
    }

    private function createMockLaporan()
    {
        return (object) [
            'laporanUnitKerjas' => collect([
                (object) [
                    'imutPenilaians' => collect([
                        (object) [
                            'numerator_value' => 80,
                            'denominator_value' => 100,
                            'profile' => (object) [
                                'target_value' => 75,
                                'target_operator' => '>=',
                                'imutData' => (object) [
                                    'categories' => (object) [
                                        'short_name' => 'KAT1'
                                    ]
                                ]
                            ]
                        ]
                    ])
                ]
            ])
        ];
    }

    private function createMockLaporanWithDates($start, $end)
    {
        return (object) [
            'assessment_period_start' => $start,
            'assessment_period_end' => $end,
        ];
    }
}
