<?php

namespace App\Filament\Resources\LaporanImutResource\Pages\Helpers\Actions;

use App\Models\LaporanImut;
use App\Models\LaporanImutAutoGenerationSetting;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class AutoGenerationSettingsActionHelper
{
    public static function fillFormData(): array
    {
        return LaporanImutAutoGenerationSetting::getInstance()->toArray();
    }

    public static function handleSave(array $data): void
    {
        $data['schedule_day_of_month'] = isset($data['schedule_day_of_month']) && is_numeric($data['schedule_day_of_month'])
            ? max(1, min(28, (int) $data['schedule_day_of_month']))
            : 1;

        $data['schedule_run_time'] = self::normalizeTime($data['schedule_run_time'] ?? '01:00');

        $data['back_data_entry_duration'] = isset($data['back_data_entry_duration']) && is_numeric($data['back_data_entry_duration'])
            ? (int) $data['back_data_entry_duration']
            : 6;

        $data['recommendation_analysis_duration'] = isset($data['recommendation_analysis_duration']) && is_numeric($data['recommendation_analysis_duration'])
            ? (int) $data['recommendation_analysis_duration']
            : 2;

        $settings = LaporanImutAutoGenerationSetting::getInstance();
        $settings->fill($data);
        $settings->updated_by = Auth::id();
        $settings->save();

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan')
            ->success()
            ->send();
    }

    public static function runNow(array $data = []): void
    {
        $choice = $data['manual_run_choice'] ?? 'create_current_month';

        if ($choice === 'cancel') {
            Notification::make()
                ->title('Eksekusi dibatalkan')
                ->body('Tidak ada laporan yang dibuat.')
                ->warning()
                ->send();

            return;
        }

        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        $alreadyExists = LaporanImut::withTrashed()
            ->where('report_month', $month)
            ->where('report_year', $year)
            ->exists();

        if ($alreadyExists) {
            Notification::make()
                ->title('Laporan bulan ini sudah ada')
                ->body("Laporan untuk {$now->translatedFormat('F Y')} tidak dibuat ulang untuk mencegah overwrite.")
                ->warning()
                ->send();

            return;
        }

        $exitCode = Artisan::call('laporan:generate-monthly', [
            '--month' => $month,
            '--year' => $year,
            '--auto-calculate' => true,
        ]);

        $output = trim(Artisan::output());
        $shortOutput = str($output)->limit(2000)->toString();

        if ($exitCode === 0) {
            Notification::make()
                ->title('Scheduler berhasil dijalankan sekarang')
                ->body($shortOutput !== '' ? $shortOutput : 'Command selesai tanpa output.')
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('Scheduler gagal dijalankan')
            ->body($shortOutput !== '' ? $shortOutput : 'Periksa log aplikasi untuk detail error.')
            ->danger()
            ->send();
    }

    private static function normalizeTime(string $time): string
    {
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)/', $time, $matches)) {
            return $matches[1] . ':' . $matches[2];
        }

        return '01:00';
    }
}
