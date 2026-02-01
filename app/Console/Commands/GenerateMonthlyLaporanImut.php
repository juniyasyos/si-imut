<?php

namespace App\Console\Commands;

use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Models\User;
use App\Jobs\ProsesPenilaianImut;
use App\Services\DailyReportAggregationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

    public function __construct(DailyReportAggregationService $aggregationService)
    {
        parent::__construct();
        $this->aggregationService = $aggregationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Monthly Laporan IMUT Generation...');
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

        // Check if report already exists
        $existingReport = LaporanImut::where('report_month', $month)
            ->where('report_year', $year)
            ->first();

        if ($existingReport) {
            $this->warn("⚠️  Report for {$monthName} {$year} already exists: \"{$existingReport->name}\"");

            if (!$this->confirm('Do you want to continue anyway (will create duplicate)?', false)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Create the report
        try {
            DB::beginTransaction();

            // Get all unit kerjas (no active scope, get all)
            $allUnitKerjas = UnitKerja::pluck('id')->toArray();

            if (empty($allUnitKerjas)) {
                $this->error('❌ No Unit Kerja found!');
                return Command::FAILURE;
            }

            $this->info("📊 Found " . count($allUnitKerjas) . " Unit Kerja");

            // Calculate assessment period (same month)
            $assessmentStart = Carbon::create($year, $month, 1)->startOfMonth();
            $assessmentEnd = Carbon::create($year, $month, 1)->endOfMonth();

            // Get system admin user for created_by
            $systemUser = User::where('name', 'admin')->orWhere('email', 'admin@example.com')->first();
            if (!$systemUser) {
                $systemUser = User::first(); // Fallback to first user
            }

            if (!$systemUser) {
                $this->error('❌ No user found in system!');
                return Command::FAILURE;
            }

            // Create LaporanImut
            $laporan = LaporanImut::create([
                'name' => "Laporan IMUT {$monthName} {$year} (Auto-Generated)",
                'report_month' => $month,
                'report_year' => $year,
                'assessment_period_start' => $assessmentStart,
                'assessment_period_end' => $assessmentEnd,
                'is_auto_generated' => true,
                'created_by' => $systemUser->id,
            ]);

            // Attach all unit kerjas
            $laporan->unitKerjas()->attach($allUnitKerjas);

            DB::commit();

            $this->info("✅ Report created successfully!");
            $this->info("   ID: {$laporan->id}");
            $this->info("   Name: {$laporan->name}");
            $this->newLine();

            // Dispatch job to create penilaian records
            $this->info("🔄 Dispatching job to create penilaian records...");
            ProsesPenilaianImut::dispatch($laporan->id);
            $this->info("✅ Job dispatched successfully!");
            $this->newLine();

            // Auto-calculate from daily reports if requested
            if ($this->option('auto-calculate')) {
                $this->info("🧮 Calculating from daily reports...");
                $this->newLine();

                // Wait a bit for the job to complete (in production, use queue monitoring)
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

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Failed to create report: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
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
