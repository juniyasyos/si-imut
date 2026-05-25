<?php

namespace App\Jobs;

use Throwable;
use Exception;
use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\LaporanImutResource;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\LaporanImutProfile;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProsesPenilaianImut implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $laporanId) {}

    public function handle(): void
    {
        try {
            DB::transaction(function () {
                $laporan = LaporanImut::with([
                    'unitKerjas.imutData.profiles',
                    'unitKerjas.users',
                    'selectedProfiles'
                ])->findOrFail($this->laporanId);

                $indikatorKurangProfil = [];
                $profilTerpilih = [];

                // Log::info("ProsesPenilaianImut [{$laporan->id}]: Starting for {$laporan->name}");
                // Log::info("ProsesPenilaianImut [{$laporan->id}]: Period {$laporan->report_month}/{$laporan->report_year}, Assessment: {$laporan->assessment_period_start} to {$laporan->assessment_period_end}");
                // Log::info("ProsesPenilaianImut [{$laporan->id}]: Total unit kerjas: " . $laporan->unitKerjas->count());

                foreach ($laporan->unitKerjas as $unitKerja) {
                    Log::info("ProsesPenilaianImut [{$laporan->id}]: Processing unit '{$unitKerja->unit_name}', ImutData count: " . $unitKerja->imutData->count());

                    $laporanUnitKerja = LaporanUnitKerja::firstOrCreate([
                        'laporan_imut_id' => $laporan->id,
                        'unit_kerja_id'   => $unitKerja->id,
                    ]);

                    foreach ($unitKerja->imutData as $imutData) {
                        if (! $imutData->status) {
                            Log::info("ProsesPenilaianImut [{$laporan->id}]: Skipped '{$imutData->title}' - status inactive");
                            continue;
                        }

                        // VALIDASI: Cek apakah IMUT data benar-benar milik unit kerja
                        $isValidOwnership = $unitKerja->imutData()
                            ->where('imut_data.id', $imutData->id)
                            ->exists();

                        if (!$isValidOwnership) {
                            Log::warning(
                                "ProsesPenilaianImut [{$laporan->id}]: REJECTED '{$imutData->title}' - " .
                                    "IMUT data ID {$imutData->id} NOT owned by unit '{$unitKerja->unit_name}'",
                                ['unit_id' => $unitKerja->id]
                            );
                            continue;
                        }

                        // Cari profil yang tepat untuk periode laporan ini
                        $selectedProfile = $this->findValidProfileForReport($imutData, $laporan);

                        if (!$selectedProfile) {
                            $indikatorKurangProfil[] = $imutData->title;
                            Log::warning("ProsesPenilaianImut [{$laporan->id}]: No valid profile for '{$imutData->title}'", [
                                'assessment_start' => $laporan->assessment_period_start?->toDateString(),
                                'assessment_end' => $laporan->assessment_period_end?->toDateString(),
                                'imut_data_id' => $imutData->id
                            ]);
                            continue;
                        }

                        // Track profil yang digunakan untuk laporan ini
                        $this->trackSelectedProfile($laporan, $imutData, $selectedProfile, $profilTerpilih);

                        // Buat penilaian
                        ImutPenilaian::firstOrCreate([
                            'imut_profil_id'        => $selectedProfile->id,
                            'laporan_unit_kerja_id' => $laporanUnitKerja->id,
                        ]);
                    }
                }

                // Log profil yang digunakan untuk transparansi
                $this->logSelectedProfiles($profilTerpilih, $laporan);

                // CLEANUP: Hapus orphaned ImutPenilaian yang tidak konsisten
                $cleanupResult = $this->cleanupOrphanedPenilaian($laporan);
                if ($cleanupResult['cleaned'] > 0) {
                    Log::info("ProsesPenilaianImut [{$laporan->id}]: Removed {$cleanupResult['cleaned']} orphaned records");
                }

                // Notifikasi ke pembuat laporan jika ada indikator tanpa profil
                if (! empty($indikatorKurangProfil)) {
                    $this->sendMissingProfileNotification($indikatorKurangProfil, $laporan);
                }

                // Notifikasi umum ke semua user unit kerja
                $this->sendGeneralNotification($laporan);
            });

            // Notifikasi akhir proses penilaian ke pembuat laporan
            $this->sendCompletionNotification();
        } catch (Throwable $e) {
            Log::error('Job ProsesPenilaianImut gagal: ' . $e->getMessage(), [
                'laporan_id' => $this->laporanId,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * CLEANUP: Hapus orphaned ImutPenilaian yang tidak konsisten dengan relasi unit kerja
     */
    private function cleanupOrphanedPenilaian(LaporanImut $laporan): array
    {
        try {
            // Identifikasi dan hapus penilaian dengan IMUT data yang tidak milik unit kerja
            // Gunakan NOT EXISTS untuk cek validasi dengan lebih clean
            $orphanedIds = DB::table('laporan_unit_kerjas')
                ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
                ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
                ->where('laporan_unit_kerjas.laporan_imut_id', $laporan->id)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('imut_data_unit_kerja')
                        ->whereColumn('imut_data_unit_kerja.unit_kerja_id', 'laporan_unit_kerjas.unit_kerja_id')
                        ->whereColumn('imut_data_unit_kerja.imut_data_id', 'imut_profil.imut_data_id');
                })
                ->pluck('imut_penilaians.id')
                ->toArray();

            if (empty($orphanedIds)) {
                return ['cleaned' => 0, 'ids' => []];
            }

            // Hapus orphaned records
            ImutPenilaian::whereIn('id', $orphanedIds)->delete();

            Log::info("ProsesPenilaianImut [{$laporan->id}]: Cleaned up orphaned penilaian", [
                'count' => count($orphanedIds),
                'ids' => $orphanedIds
            ]);

            return [
                'cleaned' => count($orphanedIds),
                'ids' => $orphanedIds
            ];
        } catch (Exception $e) {
            Log::error("ProsesPenilaianImut [{$laporan->id}]: Cleanup failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['cleaned' => 0, 'ids' => []];
        }
    }

    /**
     * Cari profil yang valid untuk periode laporan
     */
    private function findValidProfileForReport($imutData, $laporan)
    {
        // Prioritas 1: Cek apakah sudah ada profil yang dipilih khusus untuk laporan ini
        $existingSelection = LaporanImutProfile::where('laporan_imut_id', $laporan->id)
            ->where('imut_data_id', $imutData->id)
            ->with('imutProfile')
            ->first();

        if ($existingSelection && $existingSelection->imutProfile) {
            return $existingSelection->imutProfile;
        }

        // Prioritas 2: Cari profil yang valid untuk ASSESSMENT PERIOD (bukan report_month/year)
        // Karena assessment period lebih fleksibel dan bisa overlap dengan valid_from/valid_until profile
        $validProfile = $imutData->profiles()
            ->validForPeriod(
                $laporan->assessment_period_start,
                $laporan->assessment_period_end
            )
            ->orderBy('valid_from', 'desc') // Pilih yang paling baru berlaku
            ->first();

        return $validProfile;
    }

    /**
     * Track profil yang dipilih untuk laporan
     */
    private function trackSelectedProfile($laporan, $imutData, $selectedProfile, &$profilTerpilih)
    {
        // Simpan record tracking jika belum ada
        LaporanImutProfile::firstOrCreate([
            'laporan_imut_id' => $laporan->id,
            'imut_data_id'    => $imutData->id,
        ], [
            'imut_profil_id'      => $selectedProfile->id,
            'selected_at'         => now(),
            'selection_metadata'  => [
                'selection_method' => 'auto_by_period',
                'profile_version'  => $selectedProfile->version,
                'valid_from'       => $selectedProfile->valid_from?->toDateString(),
                'valid_until'      => $selectedProfile->valid_until?->toDateString(),
                'report_period'    => [
                    'start' => $laporan->assessment_period_start->toDateString(),
                    'end'   => $laporan->assessment_period_end->toDateString(),
                ]
            ]
        ]);

        // Track untuk logging
        $profilTerpilih[] = [
            'imut_data' => $imutData->title,
            'profile_version' => $selectedProfile->version,
            'valid_period' => $selectedProfile->valid_from . ' - ' . ($selectedProfile->valid_until ?? 'selamanya')
        ];
    }

    /**
     * Log profil yang dipilih untuk transparansi
     */
    private function logSelectedProfiles($profilTerpilih, $laporan)
    {
        if (!empty($profilTerpilih)) {
            Log::info("Profil terpilih untuk laporan {$laporan->name}:", [
                'laporan_id' => $laporan->id,
                'period' => $laporan->assessment_period_start . ' - ' . $laporan->assessment_period_end,
                'selected_profiles' => $profilTerpilih
            ]);
        }
    }

    /**
     * Kirim notifikasi untuk indikator tanpa profil
     */
    private function sendMissingProfileNotification($indikatorKurangProfil, $laporan)
    {
        collect($indikatorKurangProfil)
            ->unique()
            ->each(function (string $title) use ($laporan) {
                Notification::make()
                    ->title('⚠️ Indikator Belum Memiliki Profil Valid')
                    ->body("Indikator {$title} tidak memiliki profil yang valid untuk periode laporan {$laporan->assessment_period_start->format('M Y')} - {$laporan->assessment_period_end->format('M Y')}.")
                    ->icon('heroicon-m-exclamation-triangle')
                    ->color('warning')
                    ->persistent()
                    ->sendToDatabase($laporan->createdBy);
            });
    }

    /**
     * Kirim notifikasi umum ke user unit kerja
     */
    private function sendGeneralNotification($laporan)
    {
        $users = $laporan->unitKerjas->flatMap->users->unique('id');

        foreach ($users as $user) {
            Notification::make()
                ->title('📄 Laporan Baru Dibuat')
                ->body("Laporan {$laporan->name} untuk periode {$laporan->assessment_period_start->format('M Y')} membutuhkan perhatian unit kerja Anda.")
                ->icon('heroicon-m-clipboard-document-check')
                ->color('success')
                ->persistent()
                ->sendToDatabase($user);
        }
    }

    /**
     * Kirim notifikasi selesai
     */
    private function sendCompletionNotification()
    {
        $laporan = LaporanImut::findOrFail($this->laporanId);

        Notification::make()
            ->title('✅ Proses Penilaian Selesai')
            ->body('Semua data penilaian berhasil dibuat dengan profil yang sesuai periode laporan.')
            ->status('success')
            ->sendToDatabase($laporan->createdBy);
    }
}
