<?php

namespace Tests\Unit\Services\Data;

use Tests\TestCase;
use App\Services\Support\DateFormattingService;
use Illuminate\Support\Collection;

class DateFormattingServiceTest extends TestCase
{
    private DateFormattingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DateFormattingService();
    }

    /** @test */
    public function it_generates_time_labels_from_laporans(): void
    {
        // Arrange
        $laporans = collect([
            (object) [
                'assessment_period_start' => '2025-01-01',
                'assessment_period_end' => '2025-01-31'
            ],
            (object) [
                'assessment_period_start' => '2025-02-01',
                'assessment_period_end' => '2025-02-28'
            ]
        ]);

        // Act
        $result = $this->service->generateTimeLabels($laporans);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // Debug - Let's see actual output
        // Different months should show full range format: "1 Februari - 28 Februari 2025"
        $this->assertContains('1 - 31 Januari 2025', $result);
        // February spans same month, so should be: "1 - 28 Februari 2025"
        $this->assertContains('1 - 28 Februari 2025', $result);
    }

    /** @test */
    public function it_formats_same_month_period_correctly(): void
    {
        // Act
        $result = $this->service->formatPeriodLabel('2025-01-15', '2025-01-20');

        // Assert
        $this->assertEquals('15 - 20 Januari 2025', $result);
    }

    /** @test */
    public function it_formats_different_month_period_correctly(): void
    {
        // Act
        $result = $this->service->formatPeriodLabel('2025-01-15', '2025-02-20');

        // Assert
        $this->assertEquals('15 Januari - 20 Februari 2025', $result);
    }

    /** @test */
    public function it_handles_null_dates_gracefully(): void
    {
        // Act
        $result1 = $this->service->formatPeriodLabel(null, '2025-01-20');
        $result2 = $this->service->formatPeriodLabel('2025-01-15', null);
        $result3 = $this->service->formatPeriodLabel(null, null);

        // Assert
        $this->assertEquals('Tidak diketahui', $result1);
        $this->assertEquals('Tidak diketahui', $result2);
        $this->assertEquals('Tidak diketahui', $result3);
    }

    /** @test */
    public function it_formats_single_date_correctly(): void
    {
        // Act
        $result = $this->service->formatDisplayDate('2025-01-15');

        // Assert
        $this->assertEquals('15 Januari 2025', $result);
    }

    /** @test */
    public function it_handles_invalid_date_format(): void
    {
        // Act
        $result = $this->service->formatDisplayDate('invalid-date');

        // Assert
        $this->assertEquals('Format tidak valid', $result);
    }

    /** @test */
    public function it_formats_date_range_correctly(): void
    {
        // Act
        $result1 = $this->service->formatDateRange('2025-01-15', '2025-01-20');
        $result2 = $this->service->formatDateRange(null, '2025-01-20');
        $result3 = $this->service->formatDateRange('2025-01-15', null);
        $result4 = $this->service->formatDateRange(null, null);

        // Assert
        $this->assertEquals('15 - 20 Januari 2025', $result1);
        $this->assertEquals('Sampai 20 Januari 2025', $result2);
        $this->assertEquals('Dari 15 Januari 2025', $result3);
        $this->assertEquals('Periode tidak ditentukan', $result4);
    }

    /** @test */
    public function it_generates_month_labels_correctly(): void
    {
        // Act
        $result = $this->service->generateMonthLabels(2025, 1, 3);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(['Januari 2025', 'Februari 2025', 'Maret 2025'], $result);
    }

    /** @test */
    public function it_checks_date_within_period_correctly(): void
    {
        // Act & Assert
        $this->assertTrue($this->service->isDateInPeriod('2025-01-15', '2025-01-01', '2025-01-31'));
        $this->assertFalse($this->service->isDateInPeriod('2025-02-15', '2025-01-01', '2025-01-31'));
    }

    /** @test */
    public function it_handles_invalid_dates_in_period_check(): void
    {
        // Act & Assert
        $this->assertFalse($this->service->isDateInPeriod('invalid', '2025-01-01', '2025-01-31'));
        $this->assertFalse($this->service->isDateInPeriod('2025-01-15', 'invalid', '2025-01-31'));
    }

    /** @test */
    public function it_calculates_period_duration_correctly(): void
    {
        // Act
        $result = $this->service->calculatePeriodDuration('2025-01-01', '2025-01-31');

        // Assert
        $this->assertEquals(31, $result); // 31 days in January including both start and end
    }

    /** @test */
    public function it_handles_null_dates_in_duration_calculation(): void
    {
        // Act & Assert
        $this->assertEquals(0, $this->service->calculatePeriodDuration(null, '2025-01-31'));
        $this->assertEquals(0, $this->service->calculatePeriodDuration('2025-01-01', null));
        $this->assertEquals(0, $this->service->calculatePeriodDuration(null, null));
    }

    /** @test */
    public function it_handles_invalid_dates_in_duration_calculation(): void
    {
        // Act
        $result = $this->service->calculatePeriodDuration('invalid', '2025-01-31');

        // Assert
        $this->assertEquals(0, $result);
    }
}
