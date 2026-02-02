<?php

namespace App\Console\Commands;

use App\Services\LaporanImutAutoGenerationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateLaporanImutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laporan:generate 
                            {--month= : Target month (1-12)}
                            {--year= : Target year}
                            {--force : Force generation even if laporan exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Laporan IMUT automatically based on auto generation settings';

    /**
     * Execute the console command.
     */
    public function handle(LaporanImutAutoGenerationService $service): int
    {
        $this->info('🚀 Starting Laporan IMUT Auto Generation...');

        // Get target date
        $month = $this->option('month');
        $year = $this->option('year');

        if ($month && $year) {
            $targetDate = Carbon::create($year, $month, 1);
            $this->info("Target date: {$targetDate->format('F Y')}");
        } else {
            $targetDate = null;
            $this->info('Using current period based on settings');
        }

        try {
            if ($targetDate) {
                $laporan = $service->generateForMonth($targetDate);
            } else {
                $laporan = $service->generateForCurrentPeriod();
            }

            if ($laporan) {
                $this->components->success("✅ Laporan berhasil dibuat: {$laporan->name}");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['ID', $laporan->id],
                        ['Name', $laporan->name],
                        ['Month', $laporan->report_month],
                        ['Year', $laporan->report_year],
                        ['Status', $laporan->status],
                        ['Unit Kerjas', $laporan->unitKerjas->count()],
                    ]
                );

                return Command::SUCCESS;
            } else {
                $this->components->warn('⚠️  Laporan tidak dibuat (mungkin sudah ada atau auto generation dinonaktifkan)');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->components->error("❌ Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
