<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Models\ImutData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestMatrixDataDetailed extends Command
{
    protected $signature = 'test:matrix-detailed {--user-id=7} {--month=2026-04}';

    protected $description = 'Detailed test of matrix data query and database check';

    public function handle()
    {
        $userId = $this->option('user-id');
        $month = $this->option('month');

        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        Auth::setUser($user);

        $this->info("=== TESTING MATRIX DATA FOR USER {$userId} ===");
        $this->line("User: {$user->name}");
        $this->line("Month: {$month}");
        $this->line('');

        // 1. Check user units
        $this->info("1. User Units:");
        $unitKerjas = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        $this->line("   Units: " . implode(', ', $unitKerjas));

        if (empty($unitKerjas)) {
            $this->error("   User has no units!");
            return 1;
        }
        $this->line('');

        // 2. Check indicators
        $this->info("2. Form Templates (Indicators):");
        $formTemplates = FormTemplate::select([
            'form_templates.id',
            'form_templates.title',
            'form_templates.is_active',
            'imut_data.status as imut_status',
            'imut_data.is_monthly',
        ])
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->whereIn('imut_data_unit_kerja.unit_kerja_id', $unitKerjas)
            ->where('imut_data.status', true)
            ->where('imut_data.is_monthly', true)
            ->where('form_templates.is_active', true)
            ->distinct()
            ->get();

        $this->line("   Found " . $formTemplates->count() . " indicators");
        foreach ($formTemplates as $ft) {
            $this->line("   - [{$ft->id}] {$ft->title} (active: {$ft->is_active}, monthly: {$ft->is_monthly})");
        }
        $this->line('');

        // 3. Check daily report responses for this month
        $this->info("3. Daily Report Responses for {$month}:");
        $startDate = Carbon::parse($month . '-01')->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');

        $this->line("   Date range: {$startDate} to {$endDate}");

        $reportCount = DailyReportResponse::whereIn('unit_kerja_id', $unitKerjas)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->count();

        $this->line("   Total reports: {$reportCount}");

        if ($reportCount > 0) {
            // Group by form template
            $reportsByTemplate = DailyReportResponse::select([
                'form_template_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT DATE(report_date)) as days_with_data'),
            ])
                ->whereIn('unit_kerja_id', $unitKerjas)
                ->whereBetween('report_date', [$startDate, $endDate])
                ->groupBy('form_template_id')
                ->get();

            $this->line('');
            $this->line("   Reports by Template:");
            foreach ($reportsByTemplate as $report) {
                $template = FormTemplate::find($report->form_template_id);
                $this->line("   - [{$report->form_template_id}] {$template->title}: {$report->total} reports, {$report->days_with_data} days with data");
            }
        }
        $this->line('');

        // 4. Test the actual query used by MatrixDataService
        $this->info("4. Testing MatrixDataService Query:");

        $complianceSummaries = DailyReportResponse::select([
            'form_templates.id as form_template_id',
            DB::raw('DATE(daily_report_responses.report_date) as report_date'),
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(CASE WHEN compliance_status = 1 THEN 1 ELSE 0 END) as compliant_count')
        ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->whereIn('imut_data_unit_kerja.unit_kerja_id', $unitKerjas)
            ->whereIn('daily_report_responses.unit_kerja_id', $unitKerjas)
            ->whereBetween('daily_report_responses.report_date', [$startDate, $endDate])
            ->groupBy('form_templates.id', DB::raw('DATE(daily_report_responses.report_date)'))
            ->get();

        $this->line("   Query result rows: " . $complianceSummaries->count());

        if ($complianceSummaries->count() > 0) {
            $this->line("   Sample results:");
            foreach ($complianceSummaries->take(10) as $summary) {
                $this->line("   - Template {$summary->form_template_id}: {$summary->report_date} = {$summary->total_count} reports, {$summary->compliant_count} compliant");
            }
        } else {
            $this->warn("   NO RESULTS from query!");
            // Try simpler query
            $this->line('');
            $this->line("   Trying simpler query (without joins):");
            $simpleCount = DailyReportResponse::whereIn('unit_kerja_id', $unitKerjas)
                ->whereBetween('report_date', [$startDate, $endDate])
                ->count();
            $this->line("   Simple query result: {$simpleCount} records");
        }

        return 0;
    }
}
