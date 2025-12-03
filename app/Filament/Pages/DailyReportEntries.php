<?php

namespace App\Filament\Pages;

use App\Models\DailyReportEntry;
use App\Models\FormHeader;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class DailyReportEntries extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.daily-report-entries';

    protected static bool $shouldRegisterNavigation = false;

    public ?FormHeader $formHeader = null;
    public string $period;

    public string $periodName;

    public function mount(): void
    {
        $indicatorId = request()->query('indicator');
        $period = request()->query('period');

        if (!$indicatorId || !$period) {
            abort(404, 'Indicator and period parameters required');
        }

        $this->formHeader = FormHeader::with(['imutdata', 'formFields'])->findOrFail($indicatorId);
        $this->period = $period;

        $date = Carbon::createFromFormat('Y-m', $period);
        $this->periodName = $date->format('F Y');
    }

    public function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('report_date')
                ->label('Tanggal')
                ->date('d M Y')
                ->sortable()
                ->searchable(),

            TextColumn::make('created_at')
                ->label('Jam Input')
                ->time('H:i')
                ->sortable(),
        ];

        // Dynamic columns from form fields
        foreach ($this->formHeader->formFields as $field) {
            $columns[] = TextColumn::make("responses.{$field->key}")
                ->label($field->label)
                ->formatStateUsing(function ($state) use ($field) {
                    if (is_null($state)) return '-';

                    return match ($field->type) {
                        'bool' => $state ? '✅ Ya' : '❌ Tidak',
                        'date' => Carbon::parse($state)->format('d/m/Y'),
                        'checkbox' => is_array($state) ? implode(', ', $state) : $state,
                        default => $state,
                    };
                })
                ->wrap()
                ->limit(30);
        }

        $columns[] = TextColumn::make('submittedBy.name')
            ->label('Pelapor')
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->query(
                DailyReportEntry::query()
                    ->where('form_header_id', $this->formHeader->id)
                    ->whereIn('unit_kerja_id', Auth::user()->unitKerjas()->pluck('unit_kerja.id'))
                    ->whereYear('report_date', substr($this->period, 0, 4))
                    ->whereMonth('report_date', substr($this->period, 5, 2))
                    ->with(['submittedBy'])
            )
            ->columns($columns)
            ->defaultSort('report_date', 'desc')
            ->actions([
                ViewAction::make()
                    ->modalHeading('Detail Laporan')
                    ->modalWidth('2xl')
                    ->infolist(function ($record) {
                        $schema = [
                            \Filament\Infolists\Components\Section::make('Informasi')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('report_date')
                                        ->label('Tanggal')
                                        ->date('d F Y'),
                                    \Filament\Infolists\Components\TextEntry::make('created_at')
                                        ->label('Waktu Input')
                                        ->dateTime('d F Y H:i'),
                                    \Filament\Infolists\Components\TextEntry::make('submittedBy.name')
                                        ->label('Pelapor'),
                                ])
                                ->columns(3),
                        ];

                        $dataFields = [];
                        foreach ($this->formHeader->formFields as $field) {
                            $dataFields[] = \Filament\Infolists\Components\TextEntry::make("responses.{$field->key}")
                                ->label($field->label)
                                ->formatStateUsing(function ($state) use ($field) {
                                    if (is_null($state)) return '-';

                                    return match ($field->type) {
                                        'bool' => $state ? 'Ya' : 'Tidak',
                                        'date' => Carbon::parse($state)->format('d/m/Y'),
                                        'checkbox' => is_array($state) ? implode(', ', $state) : $state,
                                        default => $state,
                                    };
                                })
                                ->columnSpanFull();
                        }

                        $schema[] = \Filament\Infolists\Components\Section::make('Data Laporan')
                            ->schema($dataFields);

                        return $schema;
                    }),

                EditAction::make()
                    ->url(fn($record) => route('filament.admin.pages.edit-daily-report-entry') . '?entry=' . $record->id),

                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->emptyStateHeading('Belum ada data')
            ->emptyStateDescription('Belum ada laporan untuk periode ini');
    }

    public function getTitle(): string
    {
        return ($this->formHeader?->imutdata->title ?? 'Laporan') . ' - ' . $this->periodName;
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.daily-report-dashboard') => 'Laporan Harian',
            route('filament.admin.pages.daily-report-periods') . '?indicator=' . $this->formHeader->id => $this->formHeader?->imutdata->title,
            '#' => $this->periodName,
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->hasRole('Unit Kerja') && $user->unitKerjas()->exists();
    }

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Tambah Entry')
                ->icon('heroicon-o-plus')
                ->url(route('filament.admin.pages.create-daily-report-entry') . '?indicator=' . $this->formHeader->id . '&period=' . $this->period)
                ->color('primary'),
        ];
    }
}
