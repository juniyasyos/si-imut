<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDailyReportEntry extends EditRecord
{
    protected static string $resource = DailyReportEntryResource::class;

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return 'Edit Laporan Harian';
    }



    /**
     * Get header actions
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye')
                ->color('info'),
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash')
                ->successNotificationTitle('Laporan berhasil dihapus'),
        ];
    }

    /**
     * Get success notification
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan berhasil diperbarui')
            ->body('Perubahan telah berhasil disimpan');
    }

    /**
     * Redirect after save
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
