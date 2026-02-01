<?php

namespace App\Console\Commands;

use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Services\DailyReportAggregationService;
use Illuminate\Console\Command;

class TestDailyReportCalculation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:daily-calculation {--laporan-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Daily Report Calculation Service';

    /**
     * Execute the console command.
     */
    public function handle(DailyReportAggregationService $service)
    {
        $this->info('🧪 Testing Daily Report Calculation Service...');
        $this->newLine();

        if ($laporanId = $this->option('laporan-id')) {
            // Test untuk laporan tertentu
            $laporan = LaporanImut::find($laporanId);

            if (!$laporan) {
                $this->error("Laporan with ID {$laporanId} not found!");
                return Command::FAILURE;
            }

            $this->testLaporan($laporan, $service);
        } else {
            // Test untuk 1 penilaian saja
            $this->testSinglePenilaian($service);
        }

        return Command::SUCCESS;
    }

    private function testLaporan(LaporanImut $laporan, DailyReportAggregationService $service)
    {
        $this->info("📋 Testing Laporan: {$laporan->name}");
        $this->info("Period: {$laporan->assessment_period_start->format('d M Y')} - {$laporan->assessment_period_end->format('d M Y')}");
        $this->newLine();

        $this->info('🔄 Calculating...');
        $results = $service->calculateForLaporan($laporan);

        $this->newLine();
        $this->info("✅ Results:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Penilaians', $results['total_penilaians']],
                ['Calculated', $results['calculated']],
                ['Skipped', $results['skipped']],
                ['Errors', count($results['errors'])],
            ]
        );

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('❌ Errors:');
            foreach ($results['errors'] as $error) {
                $this->error("  - Penilaian {$error['penilaian_id']}: {$error['error']}");
            }
        }
    }

    private function testSinglePenilaian(DailyReportAggregationService $service)
    {
        // Get first penilaian untuk test
        $penilaian = ImutPenilaian::with([
            'laporanUnitKerja.laporanImut',
            'laporanUnitKerja.unitKerja',
            'profile.formTemplates'
        ])->first();

        if (!$penilaian) {
            $this->error('No ImutPenilaian found in database!');
            return;
        }

        $this->info("📊 Testing Single Penilaian (ID: {$penilaian->id})");
        $this->info("Unit Kerja: {$penilaian->laporanUnitKerja->unitKerja->name}");
        $this->info("Indicator: {$penilaian->profile->imut_data_title}");
        $this->newLine();

        // Test calculation
        $this->info('🔄 Calculating...');
        $result = $service->calculateForPenilaian($penilaian);

        $this->newLine();
        $this->info("✅ Calculation Results:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Numerator (Perfect)', $result['numerator']],
                ['Denominator (Total)', $result['denominator']],
                ['Percentage', $result['percentage'] . '%'],
                ['Days in Period', $result['calculation_metadata']['total_days_in_period']],
                ['Days Reported', $result['calculation_metadata']['days_reported']],
                ['Days Perfect', $result['calculation_metadata']['days_perfect']],
                ['Missing Days', count($result['calculation_metadata']['missing_dates'])],
            ]
        );

        if (!empty($result['calculation_metadata']['missing_dates'])) {
            $this->newLine();
            $this->warn('⚠️  Missing Dates: ' . implode(', ', $result['calculation_metadata']['missing_dates']));
        }

        // Ask to update
        $this->newLine();
        if ($this->confirm('Do you want to update this penilaian with calculated values?', true)) {
            $service->updatePenilaian($penilaian);
            $this->info('✅ Penilaian updated successfully!');
        }
    }
}
