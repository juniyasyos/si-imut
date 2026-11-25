<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringExport;
use App\Exports\UnitKerjaRekapPeriodExport;
use App\Models\FormTemplate;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Modules\ImutMaster\Models\ImutCategory;
use App\QueryBuilders\UnitKerjaReportQueryBuilder;
use App\Services\Chart\ImutPeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Export monitoring data to Excel for a given template and month.
     */
    public function monitoringExport(Request $request, string $templateId)
    {
        try {
            $user = Auth::user();
            $month = $request->query('month', now()->format('Y-m'));

            // Parse month
            $date = Carbon::createFromFormat('Y-m', $month);

            // Use full month approach
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

            // Get template with responses
            $template = FormTemplate::with([
                'imutProfile.imutData',
                'formFields.options',
                'dailyReportResponses' => function ($query) use ($startDate, $endDate, $user) {
                    $query->whereBetween('report_date', [$startDate, $endDate])
                        ->forUserUnits($user)
                        ->with(['submittedBy', 'validator', 'unitKerja', 'fieldResponses.formField']);
                },
            ])->findOrFail($templateId);

            // Generate filename
            $filename = 'monitoring_' . $template->imutProfile->imutData->title . '_' . $month . '.xlsx';
            $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);

            return Excel::download(
                new MonitoringExport($template, $startDate, $endDate),
                $filename,
            );
        } catch (\Exception $e) {
            \Log::error('Export monitoring data failed', [
                'template_id' => $templateId,
                'user_id' => $user->id ?? null,
                'month' => $month ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel rekapitulasi capaian IMUT per unit kerja sesuai jenjang periode & filter.
     */
    public function unitKerjaRekapExport(Request $request)
    {
        $periodType = (string) $request->query('period_type', 'month');
        $selectedPeriod = $request->query('period');
        $categoryIds = array_values(array_filter(array_map('intval', (array) $request->query('category_ids', []))));
        $unitKerjaIds = array_values(array_filter(array_map('intval', (array) $request->query('unit_kerja_ids', []))));
        $filterLevel = (string) $request->query('filter_level', 'all');
        $sort = (string) $request->query('sort', 'percentage_desc');

        $laporans = LaporanImut::select(['id', 'name', 'report_month', 'report_year', 'assessment_period_start', 'assessment_period_end', 'status'])
            ->orderByDesc('assessment_period_start')
            ->get();

        $periodService = app(ImutPeriodService::class);
        $periodContext = $periodService->resolvePeriodContext(
            $laporans,
            $periodType,
            $selectedPeriod ? (string) $selectedPeriod : null
        );

        $parsedPeriod = $periodContext['selectedPeriod']
            ? $periodService->parsePeriod($periodContext['periodType'], $periodContext['selectedPeriod'])
            : ['year' => (int) now()->year, 'months' => [(int) now()->month], 'label' => '-'];

        $rows = UnitKerjaReportQueryBuilder::buildMonthlyBreakdown(
            $periodContext['laporanIds'] ?? [],
            $categoryIds,
            $unitKerjaIds
        )->get();

        $categoryLabel = empty($categoryIds)
            ? 'Semua Kategori'
            : ImutCategory::whereIn('id', $categoryIds)
                ->orderBy('category_name')
                ->get(['short_name', 'category_name'])
                ->map(fn ($category) => $category->short_name ? "{$category->short_name} ({$category->category_name})" : $category->category_name)
                ->implode(', ');

        $unitLabel = empty($unitKerjaIds)
            ? 'Semua Unit Kerja'
            : UnitKerja::whereIn('id', $unitKerjaIds)
                ->orderBy('unit_name')
                ->pluck('unit_name')
                ->implode(', ');

        $periodSlug = $periodContext['selectedPeriod'] ?: now()->format('Y_m');
        $filename = 'rekap_capaian_imut_unit_' . $periodSlug . '.xlsx';
        $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);

        return Excel::download(
            new UnitKerjaRekapPeriodExport(
                periodContext: $periodContext,
                months: $parsedPeriod['months'],
                rows: $rows,
                filterLevel: $filterLevel,
                sort: $sort,
                categoryLabel: $categoryLabel ?: 'Semua Kategori',
                unitLabel: $unitLabel ?: 'Semua Unit Kerja',
            ),
            $filename
        );
    }
}
