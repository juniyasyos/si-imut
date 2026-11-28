<?php

namespace App\Http\Controllers;

use App\Exports\ImutTidakTercapaiExport;
use App\Exports\MonitoringExport;
use App\Exports\UnitKerjaRekapPeriodExport;
use App\Filament\Widgets\ImutTidakTercapai;
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
                'formFields' => fn ($query) => $query->orderBy('order_index'),
                'formFields.options',
                'dailyReportResponses' => function ($query) use ($startDate, $endDate, $user) {
                    $query->whereBetween('report_date', [$startDate, $endDate])
                        ->when(! $user?->can('view_all_data_imut::data'), fn ($q) => $q->forUserUnits($user))
                        ->with(['submittedBy', 'validator', 'unitKerja', 'fieldResponses'])
                        ->orderBy('report_date', 'asc')
                        ->orderBy('created_at', 'asc');
                },
            ])->findOrFail($templateId);

            // Generate filename
            $title = $template->imutProfile?->imutData?->title ?? $template->title ?? 'monitoring';
            $filename = $this->sanitizeExcelFilename('monitoring_' . $title . '_' . $month . '.xlsx');

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
        $categoryIds = $this->parseIntArrayParam($request, 'category_ids');
        $unitKerjaIds = $this->parseIntArrayParam($request, 'unit_kerja_ids');
        $filterLevel = (string) $request->query('filter_level', 'all');
        $sort = (string) $request->query('sort', 'percentage_desc');

        ['periodContext' => $periodContext, 'parsedPeriod' => $parsedPeriod] = $this->resolvePeriodContextForRequest($request);

        $rows = UnitKerjaReportQueryBuilder::buildMonthlyBreakdown(
            $periodContext['laporanIds'] ?? [],
            $categoryIds,
            $unitKerjaIds
        )->get();

        $periodSlug = $periodContext['selectedPeriod'] ?: now()->format('Y_m');

        return Excel::download(
            new UnitKerjaRekapPeriodExport(
                periodContext: $periodContext,
                months: $parsedPeriod['months'],
                rows: $rows,
                filterLevel: $filterLevel,
                sort: $sort,
                categoryLabel: $this->resolveCategoryLabel($categoryIds),
                unitLabel: $this->resolveUnitLabel($unitKerjaIds),
            ),
            $this->sanitizeExcelFilename('rekap_capaian_imut_unit_' . $periodSlug . '.xlsx')
        );
    }

    /**
     * Export Excel daftar indikator mutu tidak tercapai per unit kerja sesuai jenjang periode & filter.
     */
    public function imutTidakTercapaiExport(Request $request)
    {
        $categoryIds = $this->parseIntArrayParam($request, 'category_ids');
        $filterCategoryId = (string) $request->query('category_id', 'all');
        if (empty($categoryIds) && $filterCategoryId !== 'all' && $filterCategoryId !== '') {
            $categoryIds = [(int) $filterCategoryId];
        }

        $unitKerjaIds = $this->parseIntArrayParam($request, 'unit_kerja_ids');
        $search = (string) $request->query('search', '');
        $mergeUnits = filter_var($request->query('merge_units', '1'), FILTER_VALIDATE_BOOLEAN);

        ['periodContext' => $periodContext, 'parsedPeriod' => $parsedPeriod, 'periodeText' => $periodeText] = $this->resolvePeriodContextForRequest($request);

        $rows = ImutTidakTercapai::buildMonthlyBreakdown(
            $periodContext['laporanIds'] ?? [],
            $search,
            $categoryIds,
            $unitKerjaIds
        )->get();

        ['unitGroups' => $unitGroups, 'summary' => $summary] = ImutTidakTercapai::prepareMonthlyExportData(
            $rows,
            $parsedPeriod['months']
        );
        $periodSlug = $periodContext['selectedPeriod'] ?: now()->format('Y_m');

        return Excel::download(
            new ImutTidakTercapaiExport(
                periodContext: $periodContext,
                periodeText: $periodeText,
                months: $parsedPeriod['months'],
                unitGroups: $unitGroups,
                summary: $summary,
                categoryLabel: $this->resolveCategoryLabel($categoryIds),
                unitLabel: $this->resolveUnitLabel($unitKerjaIds),
                search: $search,
                mergeUnits: $mergeUnits,
            ),
            $this->sanitizeExcelFilename('daftar_imut_tidak_tercapai_' . $periodSlug . '.xlsx')
        );
    }

    /**
     * Resolve konteks periode, daftar bulan, dan rentang tanggal dari request export.
     *
     * @return array{periodContext: array<string, mixed>, parsedPeriod: array{year: int, months: int[], label: string}, periodeText: string}
     */
    protected function resolvePeriodContextForRequest(Request $request): array
    {
        $periodType = (string) $request->query('period_type', 'month');
        $selectedPeriod = $request->query('period');

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

        $startDate = $periodContext['startDate'] ?? null;
        $endDate = $periodContext['endDate'] ?? null;
        $periodeText = ($startDate && $endDate)
            ? $startDate->translatedFormat('d F Y') . ' s/d ' . $endDate->translatedFormat('d F Y')
            : ($periodContext['label'] ?? '-');

        return compact('periodContext', 'parsedPeriod', 'periodeText');
    }

    /**
     * @return int[]
     */
    protected function parseIntArrayParam(Request $request, string $key): array
    {
        return array_values(array_filter(array_map('intval', (array) $request->query($key, []))));
    }

    /**
     * @param  int[]  $categoryIds
     */
    protected function resolveCategoryLabel(array $categoryIds): string
    {
        if (empty($categoryIds)) {
            return 'Semua Kategori';
        }

        $label = ImutCategory::whereIn('id', $categoryIds)
            ->orderBy('category_name')
            ->get(['short_name', 'category_name'])
            ->map(fn ($category) => $category->short_name ? "{$category->short_name} ({$category->category_name})" : $category->category_name)
            ->implode(', ');

        return $label ?: 'Semua Kategori';
    }

    /**
     * @param  int[]  $unitKerjaIds
     */
    protected function resolveUnitLabel(array $unitKerjaIds): string
    {
        if (empty($unitKerjaIds)) {
            return 'Semua Unit Kerja';
        }

        $label = UnitKerja::whereIn('id', $unitKerjaIds)
            ->orderBy('unit_name')
            ->pluck('unit_name')
            ->implode(', ');

        return $label ?: 'Semua Unit Kerja';
    }

    protected function sanitizeExcelFilename(string $filename): string
    {
        return (string) preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);
    }
}
