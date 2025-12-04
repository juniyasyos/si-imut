<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDailyReportEntry extends ViewRecord
{
    protected static string $resource = DailyReportEntryResource::class;

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return 'Detail Laporan Harian';
    }



    /**
     * Get header actions
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Laporan')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),
            Actions\DeleteAction::make()
                ->label('Hapus Laporan')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->successNotificationTitle('Laporan berhasil dihapus')
                ->successRedirectUrl(static::getResource()::getUrl('index')),
        ];
    }
}
