<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use Filament\Resources\Pages\Page;

class ListUnitKerjaDailyReport extends Page
{
    protected static string $resource = DailyReportEntryResource::class;

    protected string $view = 'filament.resources.daily-report-entry-resource.pages.list-unit-kerja-daily-report';
}
