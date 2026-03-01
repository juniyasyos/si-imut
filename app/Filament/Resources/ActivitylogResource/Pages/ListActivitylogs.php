<?php

namespace App\Filament\Resources\ActivitylogResource\Pages;

use App\Filament\Resources\ActivitylogResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Rmsramos\Activitylog\Resources\ActivitylogResource\Pages\ListActivitylog as BaseListActivitylogResource;
use Spatie\Activitylog\Models\Activity;

class ListActivitylogs extends BaseListActivitylogResource
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label('Test')
                ->icon('heroicon-m-check')
                ->action(function () {
                    Notification::make()
                        ->title('Test Action')
                        ->body('Ini adalah aksi test.')
                        ->success()
                        ->send();
                }),
            Action::make('reset_all')
                ->label('Reset Semua Activity Log')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset Semua Activity Log')
                ->modalDescription('Apakah Anda yakin ingin menghapus semua activity log? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Hapus Semua')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    Activity::truncate();

                    Notification::make()
                        ->title('Berhasil')
                        ->body('Semua activity log telah direset.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
