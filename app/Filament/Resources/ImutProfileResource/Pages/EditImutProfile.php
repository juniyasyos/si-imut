<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutProfileResource;
use App\Models\ImutData;
use App\Models\ImutProfile;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

class EditImutProfile extends EditRecord
{
    protected static string $resource = ImutProfileResource::class;

    protected function resolveRecord(int|string $key): Model
    {
        $imutDataSlug = request()->route('imutDataSlug');

        $imutData = ImutData::where('slug', $imutDataSlug)->firstOrFail();

        return ImutProfile::where('imut_data_id', $imutData->id)
            ->where('slug', $key)
            ->firstOrFail();
    }

    public function getRedirectUrl(): string
    {

        return \App\Filament\Resources\ImutDataResource::getUrl('edit', [
            'record' => ImutData::where('id', $this->record->imut_data_id)->firstOrFail()->slug,
        ]);
    }

    public function getBreadcrumbs(): array
    {
        $imutDataSlug = request()->route('imutDataSlug');
        $imutData = ImutData::where('slug', $imutDataSlug)->first();

        $secondBreadcrumb = $imutData
            ? [ImutDataResource::getUrl('edit', ['record' => $imutData->slug]) => $imutData->title]
            : ['#' => 'Data Tidak Ditemukan'];

        return [
            ImutDataResource::getUrl('index') => 'Daftar Data IMUT',
            ...$secondBreadcrumb,
            'Edit Profil: ' . $this->record->version,
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (\Exception $e) {
            // Handle validation errors from the model
            \Filament\Notifications\Notification::make()
                ->title('Gagal memperbarui profil')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            // Re-throw to prevent record update
            throw $e;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manage_form_builder')
                ->label('Form Laporan Harian')
                ->color('info')
                ->icon('heroicon-s-document-text')
                ->url(fn($record) => ImutDataResource::getUrl('manage-form-builder', [
                    'imutDataSlug' => $record->imutData->slug,
                    'record' => $record->slug,
                ]))
                ->visible(fn($record) => static::canEditProfilIndikator($record)),

            Action::make('daily_reports')
                ->label('Lihat Laporan Harian')
                ->color('success')
                ->icon('heroicon-s-document-chart-bar')
                ->url(fn($record) => ImutDataResource::getUrl('list-daily-reports', [
                    'imutDataSlug' => $record->imutData->slug,
                    'record' => $record->slug,
                ]))
                ->visible(fn($record) => static::canEditProfilIndikator($record)),

            // DeleteAction::make()
            //     ->visible(fn() => static::canEditProfilIndikator($this->record)),
        ];
    }


    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->icon('heroicon-m-check')
                ->submit('save')
                ->visible(fn() => static::canEditProfilIndikator($this->record))
                ->keyBindings(['mod+s']),

            Action::make('cancel')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
                ->icon('heroicon-m-x-mark')
                ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from($this->previousUrl ?? static::getResource()::getUrl()) . ')')
                ->visible(fn() => static::canEditProfilIndikator($this->record))
                ->color('gray'),
        ];
    }

    public static function canEditProfilIndikator(?Model $record = null): bool
    {
        $user = Auth::user();
        return $record?->imutData->created_by === $user?->id;
    }
}
