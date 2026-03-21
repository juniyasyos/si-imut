<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PrintReportController;
use App\Http\Controllers\TableViewController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Juniyasyos\IamClient\Http\Controllers\LogoutController;
use Juniyasyos\IamClient\Http\Controllers\SsoCallbackController;
use Juniyasyos\IamClient\Http\Controllers\SsoLoginRedirectController;

// Include Livewire report routes
require_once __DIR__ . '/livewire-report.php';

// Table View Route
Route::get('/table-view', [TableViewController::class, 'index'])
    ->middleware(['auth'])
    ->name('table-view');

Route::get('/api/table-data', [TableViewController::class, 'getData'])
    ->middleware(['auth'])
    ->name('api.table-data');

// Export Monitoring Route
Route::get('/export/monitoring/{templateId}', function ($templateId) {
    try {
        $user = Auth::user();
        $month = request('month', now()->format('Y-m'));

        // Parse month
        $date = \Carbon\Carbon::createFromFormat('Y-m', $month);

        // Get period settings
        $settings = \App\Models\LaporanImutAutoGenerationSetting::getInstance();

        // Use full month approach
        $startDate = $date->copy()->startOfMonth()->startOfDay();
        $endDate = $date->copy()->endOfMonth()->endOfDay();

        // Get template with responses
        $template = \App\Models\FormTemplate::with([
            'imutProfile.imutData',
            'formFields.options',
            'dailyReportResponses' => function ($query) use ($startDate, $endDate, $user) {
                $query->whereBetween('report_date', [$startDate, $endDate])
                    ->forUserUnits($user)
                    ->with(['submittedBy', 'validator', 'unitKerja', 'fieldResponses.formField']);
            }
        ])->findOrFail($templateId);

        // Generate filename
        $filename = 'monitoring_' . $template->imutProfile->imutData->title . '_' . $month . '.xlsx';
        $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);

        // Create Excel file
        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($template, $startDate, $endDate) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle {
                private $template;
                private $startDate;
                private $endDate;

                public function __construct($template, $startDate, $endDate)
                {
                    $this->template = $template;
                    $this->startDate = $startDate;
                    $this->endDate = $endDate;
                }

                public function collection()
                {
                    $data = collect();

                    foreach ($this->template->dailyReportResponses as $response) {
                        $row = [
                            'Tanggal' => $response->report_date->format('d/m/Y'),
                            'Unit Kerja' => $response->unitKerja->unit_name ?? '',
                            'Pengumpul Data' => $response->submittedBy->name ?? '',
                            'Validator' => $response->validator->name ?? '',
                            'Status Validasi' => $response->is_validated ? 'Tervalidasi' : 'Belum Divalidasi',
                        ];

                        // Add field responses
                        foreach ($this->template->formFields as $field) {
                            $fieldResponse = $response->fieldResponses->where('form_field_id', $field->id)->first();
                            $value = '';

                            if ($fieldResponse) {
                                $fieldValue = $fieldResponse->field_value;

                                // Format value based on field type
                                switch ($field->field_type) {
                                    case 'boolean':
                                        $value = ($fieldValue == 1 || $fieldValue === true || $fieldValue === '1') ? 'Ya' : 'Tidak';
                                        break;

                                    case 'single_select':
                                    case 'multi_select':
                                        if (is_array($fieldValue)) {
                                            $selectedOptions = [];
                                            foreach ($fieldValue as $optionValue) {
                                                $option = $field->options->firstWhere('option_value', $optionValue);
                                                if ($option) {
                                                    $selectedOptions[] = $option->option_text;
                                                }
                                            }
                                            $value = implode(', ', $selectedOptions);
                                        } else {
                                            $option = $field->options->firstWhere('option_value', $fieldValue);
                                            $value = $option ? $option->option_text : $fieldValue;
                                        }
                                        break;

                                    case 'time_duration':
                                    case 'time_range':
                                        if (is_array($fieldValue)) {
                                            if (isset($fieldValue['start_time']) && isset($fieldValue['end_time'])) {
                                                $value = $fieldValue['start_time'] . ' - ' . $fieldValue['end_time'];
                                            } elseif (isset($fieldValue['duration'])) {
                                                $value = $fieldValue['duration'] . ' menit';
                                            } else {
                                                $value = json_encode($fieldValue);
                                            }
                                        } else {
                                            $value = $fieldValue;
                                        }
                                        break;

                                    case 'number':
                                        $value = is_numeric($fieldValue) ? number_format($fieldValue, 0, ',', '.') : $fieldValue;
                                        break;

                                    case 'date':
                                        if ($fieldValue && strtotime($fieldValue)) {
                                            $value = date('d/m/Y', strtotime($fieldValue));
                                        } else {
                                            $value = $fieldValue;
                                        }
                                        break;

                                    default:
                                        if (is_array($fieldValue)) {
                                            $value = json_encode($fieldValue);
                                        } else {
                                            $value = $fieldValue;
                                        }
                                        break;
                                }
                            }

                            $row[$field->field_label] = $value;
                        }

                        $data->push($row);
                    }

                    return $data;
                }

                public function headings(): array
                {
                    $headings = [
                        'Tanggal',
                        'Unit Kerja',
                        'Pengumpul Data',
                        'Validator',
                        'Status Validasi'
                    ];

                    // Add field headings
                    foreach ($this->template->formFields as $field) {
                        $headings[] = $field->field_label;
                    }

                    return $headings;
                }

                public function title(): string
                {
                    return substr($this->template->imutProfile->imutData->title, 0, 31);
                }
            },
            $filename
        );
    } catch (\Exception $e) {
        \Log::error('Export monitoring data failed', [
            'template_id' => $templateId,
            'user_id' => $user->id ?? null,
            'month' => $month,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // For file download endpoints, redirect back with error message
        return redirect()->back()->with('error', 'Gagal export data: ' . $e->getMessage());
    }
})->middleware(['auth'])->name('export.monitoring');

// Print Report Routes
// Route::prefix('print')->name('print.')->group(function () {
//     // Preview dengan dummy data
//     Route::get('/preview/imut-data-report', [PrintReportController::class, 'previewImutDataReport'])
//         ->name('preview.imut-data-report');

//     Route::get('/preview/imut-indicator-report', [PrintReportController::class, 'previewImutIndicatorReport'])
//         ->name('preview.imut-indicator-report')
//         ->middleware(['auth', 'can:view_all_data_imut::data']);

//     // Print real data (dengan laporan_id)
//     Route::get('/imut-data-report', [PrintReportController::class, 'printImutDataReport'])
//         ->name('imut-data-report');

//     Route::get('/imut-indicator-report', [PrintReportController::class, 'printImutIndicatorReport'])
//         ->name('imut-indicator-report');
// });

Route::middleware(['web'])->group(function () {
    // Root route redirect

    Route::get('/', function () {
        // If authenticated, go to admin dashboard
        if (Auth::check()) {
            return redirect('/siimut');
        }

        // If not authenticated, check SSO mode
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        if ($ssoEnabled) {
            // Production: Redirect to SSO login
            return redirect()->route('iam.sso.login');
        } else {
            // Development: Redirect to custom login
            return redirect('/siimut/login');
        }
    })->name('home');

    // Unified login entrypoint (flexible between SSO and Filament login)
    // - In SSO mode, redirects to SSO (IAM) login
    // - In local/dev mode, redirects to Filament's login page
    Route::get('/login', SsoLoginRedirectController::class)->name('login');

    // SSO Routes - with middleware to redirect to Filament login when SSO is disabled
    Route::middleware([\App\Http\Middleware\RedirectIfSsoDisabled::class])->group(function () {
        Route::get('/sso/login', SsoLoginRedirectController::class)->name('sso.login');
        Route::get('/sso/callback', SsoCallbackController::class)->name('sso.callback');
        Route::view('/sso/status', 'auth-status')->name('sso.status');
    });

    Route::post('/logout', LogoutController::class)->name('logout');

    // Fallback for legacy login URLs when SSO is enabled.
    // This prevents 404 when users hit `/siimut/login` or `/admin/login` in production.
    Route::fallback(function (Request $request) {
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);
        $path = trim($request->path(), '/');

        if (in_array($path, ['siimut/login', 'admin/login'], true) && $ssoEnabled) {
            return redirect('/login');
        }

        if ($path === 'login' && ! $ssoEnabled) {
            return redirect(\Filament\Facades\Filament::getLoginUrl());
        }

        abort(404);
    });

    // Debug routes - available in all modes
    Route::get('/debug-session', function () {
        return response()->json([
            'sso_enabled' => config('iam.enabled', false) || env('USE_SSO', false),
            'app_env' => config('app.env'),
            'session_id' => session()->getId(),
            'session_started' => session()->isStarted(),
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
            'auth_user' => Auth::user(),
            'session_data' => session()->all(),
            'cookies' => request()->cookies->all(),
            'laravel_session_cookie' => request()->cookie('laravel_session'),
        ]);
    })->name('debug.session');
});
