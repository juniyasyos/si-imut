<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormHeader;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDailyReportEntry extends CreateRecord
{
    protected static string $resource = DailyReportEntryResource::class;

    protected static bool $canCreateAnother = false;

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        $indicatorId = request()->query('indicator');
        
        if ($indicatorId) {
            $formHeader = FormHeader::with('imutdata')->find($indicatorId);
            if ($formHeader && $formHeader->imutdata) {
                return 'Input Laporan: ' . $formHeader->imutdata->title;
            }
        }
        
        return 'Buat Laporan Harian';
    }
    
    /**
     * Get page subheading
     */
    public function getSubheading(): ?string
    {
        $indicatorId = request()->query('indicator');
        
        if ($indicatorId) {
            $formHeader = FormHeader::with('imutdata.imutKategori')->find($indicatorId);
            if ($formHeader) {
                $category = $formHeader->imutdata->imutKategori->title ?? null;
                $desc = $formHeader->description;
                
                $parts = [];
                if ($category) {
                    $parts[] = "Kategori: {$category}";
                }
                if ($desc) {
                    $parts[] = $desc;
                }
                
                return implode(' — ', $parts);
            }
        }
        
        return null;
    }

    /**
     * Mutate form data before creating record
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if (!$user) {
            Notification::make()
                ->title('Gagal membuat laporan')
                ->body('Anda harus login terlebih dahulu')
                ->danger()
                ->send();

            $this->halt();
        }

        /** @var \App\Models\User $user */
        $unitKerjaId = $user->unitKerjas()->first()?->id;

        if (!$unitKerjaId) {
            Notification::make()
                ->title('Gagal membuat laporan')
                ->body('Anda tidak terdaftar di unit kerja mana pun')
                ->danger()
                ->send();

            $this->halt();
        }

        $data['unit_kerja_id'] = $unitKerjaId;
        $data['submitted_by'] = Auth::id();
        $data['entry_time'] = now();

        return $data;
    }

    /**
     * Get success notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan berhasil dibuat')
            ->body('Laporan harian telah berhasil disimpan');
    }

    /**
     * Redirect after create
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
