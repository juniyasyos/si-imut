<?php

namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends \Filament\Pages\Dashboard
{
    /**
     * @var view-string
     */
    protected string $view = 'filament.pages.dashboard-custom';
}
