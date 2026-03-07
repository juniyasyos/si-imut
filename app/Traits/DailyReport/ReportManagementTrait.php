<?php

namespace App\Traits\DailyReport;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ReportManagementTrait
{
    /**
     * Create new report - redirect to create page
     */
    public function createNewReport(): void
    {
        if ($this->selectedIndicatorId && $this->selectedDate) {
            // Check if the selected date is within the back-entry window
            $backDays = \App\Models\LaporanImutAutoGenerationSetting::getInstance()->getBackDataEntryDays();
            $sixDaysAgo = now()->subDays($backDays)->startOfDay();
            $isLocked = \Carbon\Carbon::parse($this->selectedDate)->startOfDay()->lt($sixDaysAgo);

            if ($isLocked) {
                \Filament\Notifications\Notification::make()
                    ->title('Data Terkunci')
                    ->body('Periode entri data untuk tanggal ini telah berakhir.')
                    ->danger()
                    ->send();
                return;
            }

            Log::info('createNewReport: Redirecting to create page', [
                'indicator_id' => $this->selectedIndicatorId,
                'date' => $this->selectedDate
            ]);

            $createUrl = DailyReportEntryResource::getUrl('create') . '?' . http_build_query([
                'indicator' => $this->selectedIndicatorId,
                'date' => $this->selectedDate
            ]);

            $this->redirect($createUrl);
        } else {
            Log::warning('createNewReport: Missing data', [
                'indicator_id' => $this->selectedIndicatorId,
                'date' => $this->selectedDate
            ]);

            \Filament\Notifications\Notification::make()
                ->title('Data Tidak Lengkap')
                ->body('Silakan pilih indikator dan tanggal terlebih dahulu')
                ->warning()
                ->send();
        }
    }

    /**
     * View report details
     */
    public function viewReport(int $reportId): void
    {
        $url = DailyReportEntryResource::getViewUrl(
            $reportId,
            $this->selectedIndicatorId,
            $this->selectedDate
        );
        $this->redirect($url);
    }

    /**
     * Edit report
     */
    public function editReport(int $reportId): void
    {
        // Guard: refuse edit on locked periods
        if ($this->selectedDate) {
            $backDays = \App\Models\LaporanImutAutoGenerationSetting::getInstance()->getBackDataEntryDays();
            $sixDaysAgo = now()->subDays($backDays)->startOfDay();
            if (\Carbon\Carbon::parse($this->selectedDate)->startOfDay()->lt($sixDaysAgo)) {
                \Filament\Notifications\Notification::make()
                    ->title('Data Terkunci')
                    ->body('Periode entri data untuk tanggal ini telah berakhir.')
                    ->danger()
                    ->send();
                return;
            }
        }

        $url = DailyReportEntryResource::getEditUrl(
            $reportId,
            $this->selectedIndicatorId,
            $this->selectedDate
        );
        $this->redirect($url);
    }

    /**
     * Delete report
     */
    public function deleteReport(int $reportId): void
    {
        $report = \App\Models\DailyReportResponse::findOrFail($reportId);

        // Check permissions
        $user = Auth::user();
        if (!$user || !$user->can('delete', $report)) {
            $this->addError('delete', 'Anda tidak memiliki izin untuk menghapus laporan ini.');
            return;
        }

        // Check if report can be deleted (within 24 hours)
        if ($report->created_at->diffInHours(now()) > 24) {
            $this->addError('delete', 'Laporan hanya dapat dihapus dalam 24 jam setelah dibuat.');
            return;
        }

        try {
            $report->delete();

            // Refresh matrix data and slide-over data
            $this->loadMatrixData();
            $this->loadDailyReports();

            \Filament\Notifications\Notification::make()
                ->title('Laporan berhasil dihapus')
                ->success()
                ->send();
        } catch (\Exception $e) {
            $this->addError('delete', 'Gagal menghapus laporan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete selected reports
     */
    public function bulkDeleteReports(): void
    {
        if (empty($this->selectedReports)) {
            \Filament\Notifications\Notification::make()
                ->title('Tidak ada laporan dipilih')
                ->warning()
                ->send();
            return;
        }

        $user = Auth::user();
        $deleted = 0;
        $failed = 0;

        foreach ($this->selectedReports as $reportId) {
            try {
                $report = \App\Models\DailyReportResponse::findOrFail($reportId);

                if (!$user || !$user->can('delete', $report)) {
                    $failed++;
                    continue;
                }

                $report->delete();
                $deleted++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('bulkDeleteReports: failed to delete report ' . $reportId, ['error' => $e->getMessage()]);
            }
        }

        $this->selectedReports = [];
        $this->loadMatrixData();
        $this->loadDailyReports();

        if ($failed > 0) {
            \Filament\Notifications\Notification::make()
                ->title("{$deleted} laporan dihapus, {$failed} gagal")
                ->warning()
                ->send();
        } else {
            \Filament\Notifications\Notification::make()
                ->title("{$deleted} laporan berhasil dihapus")
                ->success()
                ->send();
        }
    }

    /**
     * Toggle validation status of a report
     * @param int $reportId
     * @param string|null $status - 'valid', 'invalid', or null to clear
     */
    public function toggleValidation(int $reportId, ?string $status = null): void
    {
        $report = \App\Models\DailyReportResponse::findOrFail($reportId);

        // Check permissions - only users with validator_pic permission can validate
        $user = Auth::user();
        if (!$user || !$user->can('validate_reports')) {
            $this->addError('validation', 'Anda tidak memiliki izin untuk memvalidasi laporan ini.');
            return;
        }

        // Validate status parameter - allow null for clearing
        if ($status !== null && !in_array($status, ['valid', 'invalid'])) {
            $this->addError('validation', 'Status validasi tidak valid.');
            return;
        }

        try {
            if ($status === null) {
                // Clear validation
                $report->update([
                    'validation_status' => null,
                    'validated_by' => null,
                    'validated_at' => null,
                ]);
                $statusText = 'dihapus';
            } else {
                // Set validation
                $report->update([
                    'validation_status' => $status,
                    'validated_by' => $user->id,
                    'validated_at' => now(),
                ]);
                $statusText = $status === 'valid' ? 'valid' : 'tidak valid';
            }

            // Refresh slide-over data
            $this->loadDailyReports();

            \Filament\Notifications\Notification::make()
                ->title('Status Validasi Diubah')
                ->body('Laporan berhasil ditandai sebagai ' . $statusText)
                ->success()
                ->send();
        } catch (\Exception $e) {
            $this->addError('validation', 'Gagal mengubah status validasi: ' . $e->getMessage());
        }
    }

    /**
     * Save the report
     */
    public function saveReport(): void
    {
        try {
            $this->reportEntryForm->validate();
            $formData = $this->reportEntryForm->getState();
            $complianceData = $this->calculateCompliance($formData);

            $report = DailyReportEntry::create([
                'imut_profile_id' => $this->selectedIndicatorData['imut_profile_id'],
                'form_template_id' => $this->formTemplate->id,
                'report_date' => $this->selectedDate,
                'submitted_by_id' => Auth::id(),
                'unit_kerja_id' => Auth::user()->unitKerjas->first()?->id,
                'field_responses' => $formData['field_responses'] ?? [],
                'notes' => $formData['notes'] ?? '',
                'total_score' => $complianceData['score'],
                'compliance_status' => $complianceData['score'] >= 80,
                'status' => 'submitted',
            ]);

            \Filament\Notifications\Notification::make()
                ->title('Laporan Berhasil Disimpan')
                ->body('Laporan harian telah berhasil dibuat dengan skor kepatuhan ' . number_format($complianceData['score'], 1) . '%')
                ->success()
                ->send();

            $this->closeFormSlideOver();
            $this->loadSlideOverData();
            $this->loadMatrixData();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Gagal Menyimpan')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Calculate simple compliance score
     */
    protected function calculateCompliance(array $data): array
    {
        if (!$this->formTemplate) {
            return ['score' => 0, 'status' => 'No Template'];
        }

        $totalFields = $this->formTemplate->formFields->count();
        $filledFields = 0;

        foreach ($this->formTemplate->formFields as $field) {
            $fieldValue = $data['field_responses'][$field->field_key] ?? null;
            if (!empty($fieldValue)) {
                $filledFields++;
            }
        }

        $percentage = $totalFields > 0 ? ($filledFields / $totalFields) * 100 : 0;
        $status = $percentage >= 80 ? 'Completed' : 'Incomplete';

        return [
            'score' => $percentage,
            'status' => $status,
            'filled_fields' => $filledFields,
            'total_fields' => $totalFields
        ];
    }
}
