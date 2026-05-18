<?php

namespace App\Console\Commands;

use App\Models\LaporanImut;
use App\Models\LaporanImutAutoGenerationSetting;
use App\Models\UnitKerja;
use App\Models\User;
use App\Jobs\ProsesPenilaianImut;
use App\Services\Reporting\DailyReportAggregationService;
use App\Services\Laporan\LaporanImutAutoGenerationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyLaporanImut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laporan:generate-monthly 
                            {--month= : Month to generate (1-12), defaults to previous month}
                            {--year= : Year to generate, defaults to current year}
                            {--auto-calculate : Automatically calculate from daily reports after generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly IMUT report automatically with optional daily report calculation';

    protected DailyReportAggregationService $aggregationService;
    protected LaporanImutAutoGenerationService $autoGenerationService;

    public function __construct(
        DailyReportAggregationService $aggregationService,
        LaporanImutAutoGenerationService $autoGenerationService
    ) {
        parent::__construct();
        $this->aggregationService = $aggregationService;
        $this->autoGenerationService = $autoGenerationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Monthly Laporan IMUT Generation...');
        $this->newLine();

        // Check if auto-generation is enabled
        $settings = LaporanImutAutoGenerationSetting::getInstance();

        if (!$settings->is_enabled) {
            $this->warn('⚠️  Auto-generation is currently DISABLED in system settings');
            $this->warn('   To enable: Go to Laporan IMUT > Manajemen Otomasi Laporan > Toggle "Aktifkan Auto Generate"');
            $this->newLine();
            $this->info('💡 Tip: Scheduler will continue running but will skip generation until enabled');
            return Command::SUCCESS;
        }

        $this->info('✅ Auto-generation is ENABLED');
        $this->newLine();

        // Determine month and year
        $month = $this->option('month') ?? Carbon::now()->subMonth()->month;
        $year = $this->option('year') ?? Carbon::now()->year;

        // Validate month
        if ($month < 1 || $month > 12) {
            $this->error("❌ Invalid month: {$month}. Must be between 1 and 12.");
            return Command::FAILURE;
        }

        $monthName = $this->getMonthName($month);
        $this->info("📅 Target Period: {$monthName} {$year}");
        $this->newLine();

        // Use the auto generation service
        try {
            $date = Carbon::create($year, $month, 1);
            $laporan = $this->autoGenerationService->generateForMonth($date, $settings);

            if (!$laporan) {
                $this->warn("⚠️  Report for {$monthName} {$year} already exists or generation was skipped");
                return Command::SUCCESS;
            }

            $this->info("✅ Report created successfully!");
            $this->info("   ID: {$laporan->id}");
            $this->info("   Name: {$laporan->name}");
            $this->newLine();

            // Auto-calculate from daily reports if requested
            if ($this->option('auto-calculate') && $settings->auto_calculate) {
                $this->info("🧮 Calculating from daily reports...");
                $this->newLine();

                // Wait a bit for the job to complete
                $this->warn("⏳ Waiting 3 seconds for penilaian creation...");
                sleep(3);

                $results = $this->aggregationService->calculateForLaporan($laporan);

                $this->info("✅ Calculation completed:");
                $this->info("   Total penilaian: {$results['total_penilaians']}");
                $this->info("   Calculated: {$results['calculated']}");
                $this->info("   Skipped (no data): {$results['skipped']}");
                $this->newLine();
            }

            $this->info("🎉 Monthly report generation completed successfully!");
            Log::info("Auto-generated laporan for {$monthName} {$year}", ['laporan_id' => $laporan->id]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to create report: " . $e->getMessage());
            Log::error("Failed to auto-generate laporan: " . $e->getMessage(), ['exception' => $e]);
            return Command::FAILURE;
        }
    }

    /**
     * Get Indonesian month name
     */
    protected function getMonthName(int $month): string
    {
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        return $monthNames[$month] ?? $month;
    }
}
