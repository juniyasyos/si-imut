<?php

namespace App\Filament\Pages;

use App\Models\FormHeader;
use App\Models\DailyReportEntry;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class DailyReportPeriods extends Page
{
    protected static string $view = 'filament.resources.daily-report.pages.daily-report-periods';

    protected static bool $shouldRegisterNavigation = false;

    public ?FormHeader $formHeader = null;
    public array $periods = [];

    public function mount(): void
    {
        $indicatorId = request()->query('indicator');

        if (!$indicatorId) {
            abort(404, 'Indicator parameter required');
        }

        $this->formHeader = FormHeader::with('imutdata')->findOrFail($indicatorId);

        $this->loadPeriods();
    }

    public function loadPeriods(): void
    {
        $user = Auth::user();
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        $entries = DailyReportEntry::where('form_header_id', $this->formHeader->id)
            ->whereIn('unit_kerja_id', $unitKerjaIds)
            ->selectRaw('
                DATE_FORMAT(report_date, "%Y-%m") as period,
                MIN(report_date) as first_date,
                MAX(report_date) as last_date,
                COUNT(*) as total_entries,
                COUNT(DISTINCT DATE(report_date)) as days_with_data
            ')
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->get();

        $this->periods = $entries->map(function ($entry) {
            $date = Carbon::createFromFormat('Y-m', $entry->period);

            return [
                'period' => $entry->period,
                'month_name' => $date->format('F Y'),
                'month_short' => $date->format('M'),
                'year' => $date->format('Y'),
                'total_entries' => $entry->total_entries,
                'first_date' => Carbon::parse($entry->first_date)->format('d M'),
                'last_date' => Carbon::parse($entry->last_date)->format('d M'),
                'days_with_data' => $entry->days_with_data,
            ];
        })->toArray();
    }

    public function getTitle(): string
    {
        return $this->formHeader?->imutdata->title ?? 'Laporan Harian';
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.siimut.pages.daily-report-dashboard') => 'Laporan Harian',
            '#' => $this->getTitle(),
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->hasRole('Unit Kerja') && $user->unitKerjas()->exists();
    }
}
