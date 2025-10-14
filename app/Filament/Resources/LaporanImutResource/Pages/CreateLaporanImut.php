<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use Filament\Actions;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\LaporanImutResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateLaporanImut extends CreateRecord
{
    protected static string $resource = LaporanImutResource::class;
    protected static bool $canCreateAnother = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        // Check for existing report with same period before creating
        $existingReport = LaporanImut::where('report_month', $data['report_month'])
            ->where('report_year', $data['report_year'])
            ->first();

        if ($existingReport) {
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            $monthName = $monthNames[$data['report_month']] ?? $data['report_month'];
            
            // Show user-friendly notification instead of debug bar
            Notification::make()
                ->title('Laporan Periode Sudah Ada')
                ->body("Laporan untuk periode {$monthName} {$data['report_year']} sudah dibuat dengan nama: \"{$existingReport->name}\"")
                ->warning()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('lihat')
                        ->label('Lihat Laporan Existing')
                        ->url(route('filament.admin.resources.laporan-imuts.view', $existingReport->id))
                        ->button(),
                    \Filament\Notifications\Actions\Action::make('edit')
                        ->label('Edit Laporan Existing')
                        ->url(route('filament.admin.resources.laporan-imuts.edit', $existingReport->id))
                        ->button(),
                ])
                ->send();

            // Throw validation exception to prevent creation
            throw ValidationException::withMessages([
                'report_month' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
                'report_year' => "Laporan untuk periode {$monthName} {$data['report_year']} sudah ada.",
            ]);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (QueryException $e) {
            // Handle duplicate entry error specifically
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'unique_periode_laporan') !== false) {
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                
                $monthName = $monthNames[$data['report_month']] ?? $data['report_month'];
                
                // Find existing report to show in notification
                $existingReport = LaporanImut::where('report_month', $data['report_month'])
                    ->where('report_year', $data['report_year'])
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
                            ->url($existingReport ? 
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

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Proses Penilaian Dimulai')
            ->body('Data sedang diproses di latar belakang...')
            ->status('info')
            ->send();

        dispatch(new \App\Jobs\ProsesPenilaianImut($this->record->id));
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.laporan-imuts.index') => 'Laporan IMUT',
            null => 'Buat Laporan Baru',
        ];
    }
}
