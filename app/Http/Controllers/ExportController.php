<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringExport;
use App\Models\FormTemplate;
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
}
