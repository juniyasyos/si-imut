<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

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

        $label = $imutData
            ? "{$imutData->title}"
            : 'Data Tidak Ditemukan';

        return [
            route('filament.admin.resources.imut-datas.index') => 'Imut Datas',
            $imutData
                ? route('filament.admin.resources.imut-datas.edit', ['record' => $imutData->slug])
                : '#' => $label,
            null => 'Edit Profile | ' . $this->record->version,
        ];
    }

    // protected function handleRecordUpdate(Model $record, array $data): Model
    // {
    //     return parent::handleRecordUpdate($record, $data);
    // }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn() => static::canEditProfilIndikator($this->record)),
        ];
    }


    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save')
                ->visible(fn() => static::canEditProfilIndikator($this->record))
                ->keyBindings(['mod+s']),

            Action::make('cancel')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
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