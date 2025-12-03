<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Filament\Resources\LaporanImutResource;
use App\Jobs\ProsesPenilaianImut;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EditLaporanImut extends EditRecord
{
    protected static string $resource = LaporanImutResource::class;

    protected array $originalUnitKerjaIds = [];

    protected function resolveRecord(int|string $key): \Illuminate\Database\Eloquent\Model
    {
        return \App\Models\LaporanImut::where('slug', $key)->firstOrFail();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Simpan daftar unit_kerja_id sebelum update
        $this->originalUnitKerjaIds = $this->record->unitKerjas->pluck('id')->toArray();

        // Check for existing report with same period before updating (excluding current record)
        $existingReport = LaporanImut::where('report_month', $data['report_month'])
            ->where('report_year', $data['report_year'])
            ->where('id', '!=', $this->record->id)
            ->first();

        if ($existingReport) {
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

            $monthName = $monthNames[$data['report_month']] ?? $data['report_month'];

            // Show user-friendly notification instead of debug bar
            Notification::make()
                ->title('Laporan Periode Sudah Ada')
                ->body("Laporan untuk periode {$monthName} {$data['report_year']} sudah dibuat dengan nama: \"{$existingReport->name}\"")
                ->warning()
                ->persistent()
                // ->actions([
                //     \Filament\Notifications\Actions\Action::make('lihat')
                //         ->label('Lihat Laporan Existing')
                //         ->url(route('filament.admin.resources.laporan-imuts.view', $existingReport->id))
                //         ->button(),
                // ])
                ->send();

            // Throw validation exception to prevent update
            throw ValidationException::withMessages([
                'report_month' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
                'report_year' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
            ]);
        }

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (QueryException $e) {
            // Handle duplicate entry error specifically
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'unique_periode_laporan') !== false) {
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

                $monthName = $monthNames[$data['report_month']] ?? $data['report_month'];

                // Find existing report to show in notification
                $existingReport = LaporanImut::where('report_month', $data['report_month'])
                    ->where('report_year', $data['report_year'])
                    ->where('id', '!=', $record->id)
                    ->first();

                Notification::make()
                    ->title('Laporan Periode Sudah Ada')
                    ->body("Laporan untuk periode {$monthName} {$data['report_year']} sudah dibuat" .
                        ($existingReport ? " dengan nama: \"{$existingReport->name}\"" : '.'))
                    ->warning()
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('lihat')
                            ->label('Lihat Laporan Existing')
                            ->url(
                                $existingReport ?
                                    route('filament.admin.resources.laporan-imuts.view', $existingReport->id) :
                                    route('filament.admin.resources.laporan-imuts.index')
                            )
                            ->button(),
                    ])
                    ->send();

                throw ValidationException::withMessages([
                    'report_month' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
                    'report_year' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
                ]);
            }

            // Re-throw other exceptions
            throw $e;
        }
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->slug]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.laporan-imuts.index') => 'Laporan IMUT',
            null => 'Edit: ' . $this->record->name,
        ];
    }

    /**
     * Check if there are unit kerjas with data being removed
     */
    protected function getUnitsWithDataBeingRemoved(): array
    {
        // Get current form data
        $formData = $this->form->getState();
        $currentUnitKerjaIds = $formData['unitKerjas'] ?? [];

        // Get original unit kerja IDs
        if (empty($this->originalUnitKerjaIds)) {
            $this->originalUnitKerjaIds = $this->record->unitKerjas->pluck('id')->toArray();
        }

        $removedUnitKerjaIds = array_diff($this->originalUnitKerjaIds, $currentUnitKerjaIds);

        if (empty($removedUnitKerjaIds)) {
            return [];
        }

        $unitsWithData = [];

        foreach ($removedUnitKerjaIds as $unitKerjaId) {
            $laporanUnitKerja = LaporanUnitKerja::where('laporan_imut_id', $this->record->id)
                ->where('unit_kerja_id', $unitKerjaId)
                ->first();

            if ($laporanUnitKerja) {
                $penilaianCount = ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)->count();
                $filledCount = ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)
                    ->whereNotNull('numerator_value')
                    ->whereNotNull('denominator_value')
                    ->count();

                if ($penilaianCount > 0) {
                    $unitKerja = \App\Models\UnitKerja::find($unitKerjaId);
                    $unitsWithData[] = [
                        'id' => $unitKerjaId,
                        'name' => $unitKerja?->unit_name ?? "Unit Kerja #{$unitKerjaId}",
                        'total' => $penilaianCount,
                        'filled' => $filledCount,
                    ];
                }
            }
        }

        return $unitsWithData;
    }

    /**
     * Get all changes (added and removed units)
     */
    protected function getUnitKerjaChanges(): array
    {
        $formData = $this->form->getState();
        $currentUnitKerjaIds = $formData['unitKerjas'] ?? [];

        if (empty($this->originalUnitKerjaIds)) {
            $this->originalUnitKerjaIds = $this->record->unitKerjas->pluck('id')->toArray();
        }

        $removedIds = array_diff($this->originalUnitKerjaIds, $currentUnitKerjaIds);
        $addedIds = array_diff($currentUnitKerjaIds, $this->originalUnitKerjaIds);

        $changes = [
            'added' => [],
            'removed_empty' => [],
            'removed_with_data' => [],
        ];

        // Get added units
        foreach ($addedIds as $unitKerjaId) {
            $unitKerja = \App\Models\UnitKerja::find($unitKerjaId);
            $changes['added'][] = $unitKerja?->unit_name ?? "Unit Kerja #{$unitKerjaId}";
        }

        // Get removed units
        foreach ($removedIds as $unitKerjaId) {
            $unitKerja = \App\Models\UnitKerja::find($unitKerjaId);
            $unitName = $unitKerja?->unit_name ?? "Unit Kerja #{$unitKerjaId}";

            $laporanUnitKerja = LaporanUnitKerja::where('laporan_imut_id', $this->record->id)
                ->where('unit_kerja_id', $unitKerjaId)
                ->first();

            if ($laporanUnitKerja) {
                $penilaianCount = ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)->count();
                $filledCount = ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)
                    ->whereNotNull('numerator_value')
                    ->whereNotNull('denominator_value')
                    ->count();

                if ($penilaianCount > 0) {
                    $changes['removed_with_data'][] = [
                        'name' => $unitName,
                        'total' => $penilaianCount,
                        'filled' => $filledCount,
                        'percentage' => $penilaianCount > 0 ? round(($filledCount / $penilaianCount) * 100) : 0,
                    ];
                } else {
                    $changes['removed_empty'][] = $unitName;
                }
            } else {
                $changes['removed_empty'][] = $unitName;
            }
        }

        return $changes;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation(function () {
                    $changes = $this->getUnitKerjaChanges();
                    return !empty($changes['added']) ||
                        !empty($changes['removed_empty']) ||
                        !empty($changes['removed_with_data']);
                })
                ->modalHeading(function () {
                    $changes = $this->getUnitKerjaChanges();
                    $hasDataToDelete = !empty($changes['removed_with_data']);
                    return $hasDataToDelete ? '⚠️ Konfirmasi Penghapusan Data' : '📝 Konfirmasi Perubahan';
                })
                ->modalDescription(function () {
                    $changes = $this->getUnitKerjaChanges();

                    if (empty($changes['added']) && empty($changes['removed_empty']) && empty($changes['removed_with_data'])) {
                        return null;
                    }

                    $lines = [];
                    $lines[] = "**Ringkasan Perubahan Unit Kerja:**";
                    $lines[] = "";

                    if (!empty($changes['added'])) {
                        $lines[] = "**✅ Unit Kerja Ditambahkan (" . count($changes['added']) . "):**";
                        foreach ($changes['added'] as $unit) {
                            $lines[] = "• {$unit}";
                        }
                        $lines[] = "";
                    }

                    if (!empty($changes['removed_empty'])) {
                        $lines[] = "**➖ Unit Kerja Dihapus - Tanpa Data (" . count($changes['removed_empty']) . "):**";
                        foreach ($changes['removed_empty'] as $unit) {
                            $lines[] = "• {$unit}";
                        }
                        $lines[] = "";
                    }

                    if (!empty($changes['removed_with_data'])) {
                        $lines[] = "**🗑️ Unit Kerja Dihapus - BESERTA DATA (" . count($changes['removed_with_data']) . "):**";
                        foreach ($changes['removed_with_data'] as $unit) {
                            $lines[] = "• **{$unit['name']}**";
                            $lines[] = "  └─ {$unit['filled']}/{$unit['total']} penilaian terisi ({$unit['percentage']}%)";
                        }
                        $lines[] = "";
                        $lines[] = "⚠️ **PERINGATAN KRITIS:**";
                        $lines[] = "Semua data penilaian akan **DIHAPUS PERMANEN** dan **TIDAK DAPAT DIKEMBALIKAN!**";
                    }

                    return implode("\n", $lines);
                })
                ->modalSubmitActionLabel(function () {
                    $changes = $this->getUnitKerjaChanges();
                    $hasDataToDelete = !empty($changes['removed_with_data']);
                    return $hasDataToDelete ? 'Ya, Hapus Data & Simpan' : 'Ya, Simpan Perubahan';
                })
                ->modalCancelActionLabel('Batal')
                ->color(function () {
                    $changes = $this->getUnitKerjaChanges();
                    $hasDataToDelete = !empty($changes['removed_with_data']);
                    return $hasDataToDelete ? 'danger' : 'primary';
                })
                ->icon('heroicon-o-check'),
        ];
    }

    protected function getHeaderActions(): array
    {
        $laporan = $this->record;

        return [
            Action::make('imutDataSummary')
                ->label('Summary IMUT Data')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary')
                ->url(fn($record) => \App\Services\LaporanRedirectService::getRedirectUrlForImutData($record->id))
                ->disabled(fn($record) => is_null($record->imutPenilaians))
                ->visible(fn() => Gate::any([
                    'view_imut_data_report_laporan::imut',
                    'view_imut_data_report_detail_laporan::imut',
                ])),

            Action::make('unitKerjaSummary')
                ->label('Summary Unit Kerja')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->url(fn($record) => \App\Services\LaporanRedirectService::getRedirectUrlForUnitKerja($record->id))
                ->visible(fn() => Gate::any([
                    'view_unit_kerja_report_laporan::imut',
                    'view_unit_kerja_report_detail_laporan::imut',
                ])),
        ];
    }

    // protected function afterSave(): void
    // {
    //     $currentUnitKerjaIds = $this->record->unitKerjas()->pluck('unit_kerja_id')->toArray();

    //     $removedUnitKerjaIds = array_diff($this->originalUnitKerjaIds, $currentUnitKerjaIds);
    //     $addedUnitKerjaIds = array_diff($currentUnitKerjaIds, $this->originalUnitKerjaIds);

    //     DB::transaction(function () use ($removedUnitKerjaIds, $addedUnitKerjaIds) {
    //         // 🔴 1. Hapus data dari unit kerja yang di-uncheck
    //         foreach ($removedUnitKerjaIds as $unitKerjaId) {
    //             $laporanUnitKerja = LaporanUnitKerja::where('laporan_imut_id', $this->record->id)
    //                 ->where('unit_kerja_id', $unitKerjaId)
    //                 ->first();

    //             if ($laporanUnitKerja) {
    //                 ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)->delete();
    //                 $laporanUnitKerja->delete();
    //             }

    //             $this->record->unitKerjas()->detach($unitKerjaId);
    //         }

    //         // ✅ 2. Tambah data penilaian dan laporan_unit_kerja untuk unit kerja baru
    //         $laporan = $this->record->load('unitKerjas.imutData.latestProfile');

    //         foreach ($laporan->unitKerjas as $unitKerja) {
    //             if (! in_array($unitKerja->id, $addedUnitKerjaIds)) {
    //                 continue; // Skip unit kerja yang tidak baru
    //             }

    //             $laporanUnitKerja = LaporanUnitKerja::firstOrCreate([
    //                 'laporan_imut_id' => $laporan->id,
    //                 'unit_kerja_id' => $unitKerja->id,
    //             ]);

    //             foreach ($unitKerja->imutData as $imutData) {
    //                 $latestProfile = $imutData->latestProfile;

    //                 if (! $latestProfile) {
    //                     continue;
    //                 }

    //                 ImutPenilaian::firstOrCreate([
    //                     'imut_profil_id' => $latestProfile->id,
    //                     'laporan_unit_kerja_id' => $laporanUnitKerja->id,
    //                 ]);
    //             }
    //         }
    //     });
    // }

    protected function afterSave(): void
    {
        $newUnitKerjaIds = $this->record->unitKerjas()->pluck('unit_kerja_id')->toArray();
        $removedUnitKerjaIds = array_diff($this->originalUnitKerjaIds, $newUnitKerjaIds);

        if (!empty($removedUnitKerjaIds)) {
            $deletedStats = [];

            DB::transaction(function () use ($removedUnitKerjaIds, &$deletedStats) {
                foreach ($removedUnitKerjaIds as $unitKerjaId) {
                    $laporanUnitKerja = LaporanUnitKerja::where('laporan_imut_id', $this->record->id)
                        ->where('unit_kerja_id', $unitKerjaId)
                        ->first();

                    if ($laporanUnitKerja) {
                        $penilaianCount = ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)->count();
                        $filledCount = ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)
                            ->whereNotNull('numerator_value')
                            ->whereNotNull('denominator_value')
                            ->count();

                        if ($penilaianCount > 0) {
                            $unitKerja = \App\Models\UnitKerja::find($unitKerjaId);
                            $deletedStats[] = [
                                'name' => $unitKerja?->unit_name ?? "Unit Kerja #{$unitKerjaId}",
                                'total' => $penilaianCount,
                                'filled' => $filledCount,
                            ];
                        }

                        // Delete penilaian and laporan unit kerja
                        ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)->delete();
                        $laporanUnitKerja->delete();
                    }
                }
            });

            // Show notification about deleted data
            if (!empty($deletedStats)) {
                $body = "Data yang dihapus:\n";
                foreach ($deletedStats as $stat) {
                    $body .= "• {$stat['name']}: {$stat['filled']}/{$stat['total']} penilaian\n";
                }

                Notification::make()
                    ->title('Data Unit Kerja Berhasil Dihapus')
                    ->body($body)
                    ->success()
                    ->persistent()
                    ->send();
            }
        }

        ProsesPenilaianImut::dispatch($this->record->id);
    }
}
