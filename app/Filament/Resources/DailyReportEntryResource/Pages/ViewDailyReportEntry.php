<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormTemplate;
use App\Services\DailyReport\DailyReportEntryContextService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDailyReportEntry extends ViewRecord
{
    protected static string $resource = DailyReportEntryResource::class;

    protected static string $view = 'filament.pages.create-daily-report-entry';

    public ?FormTemplate $formTemplate = null;
    public ?string $originalIndicatorId = null;
    public ?string $originalDate = null;
    private DailyReportEntryContextService $contextService;

    public function __construct()
    {
        $this->contextService = app(DailyReportEntryContextService::class);
    }

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
        return $this->contextService->getFormTitle($this->formTemplate, 'Detail Laporan Harian');
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
        return $this->contextService->getFormattedDate($this->record->report_date->format('Y-m-d'));
    }

    /**
     * Get category badge color
     */
    public function getCategoryBadgeColor(): string
    {
        return $this->contextService->getCategoryBadgeColor($this->formTemplate);
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
