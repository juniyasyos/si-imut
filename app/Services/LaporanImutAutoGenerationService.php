<?php

namespace App\Services;

use App\Jobs\ProsesPenilaianImut;
use App\Models\LaporanImut;
use App\Models\LaporanImutAutoGenerationSetting;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LaporanImutAutoGenerationService
{
    /**
     * Generate laporan for the given month
     */
    public function generateForMonth(Carbon $date, ?LaporanImutAutoGenerationSetting $settings = null): ?LaporanImut
    {
        $settings = $settings ?? LaporanImutAutoGenerationSetting::getInstance();

        if (!$settings->isActive()) {
            Log::info('Auto generation is disabled');
            return null;
        }

        $month = $date->month;
        $year = $date->year;

        // Check if laporan already exists
        if ($this->laporanExists($month, $year)) {
            Log::info("Laporan already exists for period", ['month' => $month, 'year' => $year]);
            return null;
        }

        try {
            return DB::transaction(function () use ($month, $year, $settings) {
                // Create laporan
                $laporan = $this->createLaporan($month, $year, $settings);

                // Attach unit kerjas
                if (!empty($settings->default_unit_kerjas)) {
                    $this->attachUnitKerjas($laporan, $settings->default_unit_kerjas);
                }

                // Trigger penilaian processing job
                ProsesPenilaianImut::dispatch($laporan->id);

                // Auto calculate if enabled
                if ($settings->auto_calculate) {
                    $this->scheduleAutoCalculation($laporan);
                }

                Log::info('Laporan auto-generated successfully', [
                    'laporan_id' => $laporan->id,
                    'month' => $month,
                    'year' => $year,
                ]);

                return $laporan;
            });
        } catch (\Exception $e) {
            Log::error('Failed to auto-generate laporan', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if laporan already exists for the period
     */
    protected function laporanExists(int $month, int $year): bool
    {
        return LaporanImut::where('report_month', $month)
            ->where('report_year', $year)
            ->exists();
    }

    /**
     * Create the laporan record
     */
    protected function createLaporan(int $month, int $year, LaporanImutAutoGenerationSetting $settings): LaporanImut
    {
        $monthName = $this->getMonthName($month);

        $laporan = new LaporanImut();
        $laporan->name = "Laporan IMUT {$monthName} {$year}";
        $laporan->slug = Str::slug("laporan-imut-{$monthName}-{$year}");
        $laporan->report_month = $month;
        $laporan->report_year = $year;
        $laporan->status = $settings->auto_publish ? 'published' : 'draft';
        $laporan->description = "Laporan IMUT periode {$monthName} {$year} (dibuat otomatis oleh sistem)";

        // Set templates if configured
        if ($settings->analysis_template) {
            $laporan->analysis = $settings->analysis_template;
        }

        if ($settings->recommendation_template) {
            $laporan->recommendation = $settings->recommendation_template;
        }

        $laporan->save();

        return $laporan;
    }

    /**
     * Attach unit kerjas to laporan
     */
    protected function attachUnitKerjas(LaporanImut $laporan, array $unitKerjaIds): void
    {
        // Validate unit kerja IDs exist
        $validIds = UnitKerja::whereIn('id', $unitKerjaIds)->pluck('id')->toArray();

        if (!empty($validIds)) {
            $laporan->unitKerjas()->attach($validIds);
        }
    }

    /**
     * Schedule auto calculation job
     */
    protected function scheduleAutoCalculation(LaporanImut $laporan): void
    {
        // This will be handled by the DailyReportAggregationService
        // For now, just log it
        Log::info('Auto calculation scheduled for laporan', ['laporan_id' => $laporan->id]);
    }

    /**
     * Send reminder notifications
     */
    public function sendReminders(Carbon $date, ?LaporanImutAutoGenerationSetting $settings = null): void
    {
        $settings = $settings ?? LaporanImutAutoGenerationSetting::getInstance();

        if (!$settings->isActive()) {
            return;
        }

        // Get current month's laporan
        $laporan = LaporanImut::where('report_month', $date->month)
            ->where('report_year', $date->year)
            ->first();

        if (!$laporan) {
            return;
        }

        // Calculate days remaining until deadline
        $deadline = $this->calculateDeadline($date, $settings);
        $daysRemaining = $deadline->diffInDays($date, false);

        // Check if should send reminder
        if ($settings->shouldSendReminder($daysRemaining)) {
            $this->sendReminderNotification($laporan, $daysRemaining, $settings);
        }
    }

    /**
     * Calculate deadline date
     */
    protected function calculateDeadline(Carbon $reportDate, LaporanImutAutoGenerationSetting $settings): Carbon
    {
        return $reportDate->copy()
            ->addDays($settings->total_deadline_days);
    }

    /**
     * Send reminder notification
     */
    protected function sendReminderNotification(
        LaporanImut $laporan,
        int $daysRemaining,
        LaporanImutAutoGenerationSetting $settings
    ): void {
        $targets = $settings->notification_targets ?? [];

        $title = $daysRemaining === 0
            ? '⏰ Deadline Hari Ini!'
            : "⏰ Reminder: {$daysRemaining} Hari Lagi";

        $body = "Laporan \"{$laporan->name}\" akan jatuh tempo dalam {$daysRemaining} hari.";

        // Get recipients based on targets
        $recipients = $this->getNotificationRecipients($laporan, $targets);

        foreach ($recipients as $recipient) {
            Notification::make()
                ->title($title)
                ->body($body)
                ->warning()
                ->sendToDatabase($recipient);
        }

        Log::info('Reminder notifications sent', [
            'laporan_id' => $laporan->id,
            'days_remaining' => $daysRemaining,
            'recipients_count' => count($recipients),
        ]);
    }

    /**
     * Get notification recipients
     */
    protected function getNotificationRecipients(LaporanImut $laporan, array $targets): array
    {
        $recipients = [];

        // This can be customized based on your needs
        // For now, just get users with certain permissions
        if (in_array('pic', $targets) || in_array('all', $targets)) {
            $recipients = array_merge(
                $recipients,
                User::permission('update_laporan::imut')->get()->toArray()
            );
        }

        return $recipients;
    }

    /**
     * Get month name in Indonesian
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

    /**
     * Generate laporan for current period
     */
    public function generateForCurrentPeriod(): ?LaporanImut
    {
        $settings = LaporanImutAutoGenerationSetting::getInstance();

        // Generate for current month using full month approach
        $now = Carbon::now();
        $targetDate = $now;

        return $this->generateForMonth($targetDate, $settings);
    }
}
