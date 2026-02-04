<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormTemplate;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDailyReportEntry extends ViewRecord
{
    protected static string $resource = DailyReportEntryResource::class;

    protected static string $view = 'filament.pages.create-daily-report-entry';

    public ?FormTemplate $formTemplate = null;
    public ?string $originalIndicatorId = null;
    public ?string $originalDate = null;

    /**
     * Mount the component
     */
    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Load form template and parameters
        $this->originalIndicatorId = request()->query('indicator') ?? $this->record->form_template_id;
        $this->originalDate = request()->query('date') ?? $this->record->report_date->format('Y-m-d');

        if ($this->record->form_template_id) {
            $this->formTemplate = FormTemplate::with(['formFields.options', 'imutProfile'])->find($this->record->form_template_id);
        }
    }

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return '';
    }

    /**
     * Get the page heading
     */
    public function getHeading(): string
    {
        return '';
    }

    /**
     * Get form title for display
     */
    public function getFormTitle(): string
    {
        if ($this->formTemplate && $this->formTemplate->imutProfile) {
            return $this->formTemplate->imutProfile->title;
        }
        return 'Detail Laporan Harian';
    }

    /**
     * Get form description
     */
    public function getFormDescription(): ?string
    {
        return $this->formTemplate?->description;
    }

    /**
     * Get formatted date
     */
    public function getFormattedDate(): string
    {
        return $this->record->report_date->format('d F Y');
    }

    /**
     * Get category badge color
     */
    public function getCategoryBadgeColor(): string
    {
        if ($this->formTemplate && $this->formTemplate->imutProfile) {
            $colors = ['blue', 'green', 'purple', 'orange', 'red', 'indigo', 'pink'];
            $index = abs(crc32($this->formTemplate->imutProfile->title)) % count($colors);
            return $colors[$index];
        }
        return 'gray';
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
