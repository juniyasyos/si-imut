<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Models\FormHeader;
use App\Models\ImutData;
use Filament\Resources\Pages\Page;

class PreviewFormBuilder extends Page
{
    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.preview-form-builder';

    public ?ImutData $record = null;

    public ?FormHeader $formHeader = null;

    public function mount(): void
    {
        $imutDataId = request()->route('record');
        $this->record = ImutData::query()->where('slug', $imutDataId)->firstOrFail();

        $this->formHeader = FormHeader::where('imutdata_id', $this->record->id)
            ->with('formFields')
            ->first();
    }

    public function getBreadcrumbs(): array
    {
        return [
            ImutDataResource::getUrl('index') => 'Data IMUT',
            ImutDataResource::getUrl('edit', ['record' => $this->record->slug]) => $this->record->title,
            ImutDataResource::getUrl('manage-form-builder', ['record' => $this->record->slug]) => 'Form Builder',
            '#' => 'Preview',
        ];
    }
}
