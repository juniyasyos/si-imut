<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use App\Filament\Resources\ImutDataResource\RelationManagers\RegionTypeRelationManager;
use App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\CanNotify;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Guava\FilamentModalRelationManagers\Actions\Action\RelationManagerAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class EditImutData extends EditRecord
{
    use CanNotify;

    protected static string $resource = ImutDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('lihat_grafik')
                ->label('Lihat Grafik IMUT')
                ->color('primary')
                ->icon('heroicon-s-chart-bar')
                ->visible(fn($record) => Auth::user() && ($record->created_by === Auth::id() || Auth::user()->can('view_all_data_imut::data')))
                ->url(fn($record) => SummaryDiagram::getUrl(['record' => $record->slug])),

            ActionGroup::make([
                RelationManagerAction::make('profiles') 
                    ->slideOver()
                    ->label('Kelola Profiles')
                    ->icon('heroicon-s-user-group')
                    ->record($this->getRecord())
                    ->color('gray')
                    ->relationManager(ProfilesRelationManager::make()),

                RelationManagerAction::make('unit-kerja-relation')
                    ->slideOver()
                    ->label('Unit Kerja')
                    ->icon('heroicon-s-building-office')
                    ->record($this->getRecord())
                    ->color('gray')
                    ->relationManager(UnitKerjaRelationManager::make())
                    ->visible(fn() => Auth::user()?->can('view_all_data_imut::data')),

                RelationManagerAction::make('region-types')
                    ->slideOver()
                    ->label('Kategori Pembanding')
                    ->icon('heroicon-s-map-pin')
                    ->record($this->getRecord())
                    ->color('gray')
                    ->relationManager(RegionTypeRelationManager::make())
                    ->visible(fn($record) => $record->categories->is_benchmark_category === 1),
            ])
                ->button()
                ->label('🔧 Kelola Data')
                ->icon('heroicon-s-cog-6-tooth')
                ->color('gray'),

            $this->getDeleteAction(),
        ];
    }

    public static function canEditProfilIndikator(?Model $record = null): bool
    {
        $user = Auth::user();

        return $record?->created_by === $user?->id || $user?->can('view_all_data_imut::data') ;
    }

    protected function getFormActions(): array
    {
        $user = Auth::user();
        $record = $this->getRecord();

        $isCreator = $record?->created_by === $user?->id;

        if (! $isCreator && ! $user?->can('view_all_data_imut::data')) {
            return [];
        }

        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.siimut.resources.imut-datas.index') => 'IMUT Data',
            null => $this->record->title,
        ];
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    // 🔔 Fungsi notifikasi fleksibel menggunakan Filament Notification
    protected function notifyUser(string $type, string $message): void
    {
        Notification::make()
            ->title($message)
            ->send();
    }

    // 🗑️ Delete action
    protected function getDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->label(__('filament-forms::imut-data.actions.delete.label'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            // ->disabled(fn($record) => Auth::user()?->can('delete_imut::data') && $record->creator === Auth::id())
            ->visible(fn($record) => self::canEditProfilIndikator($record))
            ->modalHeading(__('filament-forms::imut-data.actions.delete.modal_heading'))
            ->modalDescription(__('filament-forms::imut-data.actions.delete.modal_description'))
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('danger')
            ->modalSubmitActionLabel(__('filament-forms::imut-data.actions.delete.modal_submit_label'))
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title(__('filament-forms::imut-data.notifications.deleted.title'))
                    ->body(__('filament-forms::imut-data.notifications.deleted.body'))
            );
    }
}
