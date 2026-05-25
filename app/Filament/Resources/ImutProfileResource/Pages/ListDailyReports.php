<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\Action;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use App\Filament\Resources\ImutProfileResource;
use App\Models\ImutProfile;
use App\Models\DailyReportResponse;
use App\Models\UnitKerja;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\CreateAction;
use Filament\Infolists\Components\TextEntry;

class ListDailyReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ImutProfileResource::class;

    protected string $view = 'filament.pages.list-daily-reports';

    public ?ImutProfile $record = null;

    public function mount(ImutProfile $record): void
    {
        $this->record = $record;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('report_date')
                    ->label('Tanggal Laporan')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unitKerja.unit_name')
                    ->label('Unit Kerja')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('submittedBy.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_score')
                    ->label('Total Skor')
                    ->numeric(2)
                    ->suffix('%')
                    ->color(fn($state) => $this->getScoreColor($state))
                    ->sortable(),

                TextColumn::make('compliance_status')
                    ->label('Status Kepatuhan')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Patuh' : 'Tidak Patuh')
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu Input')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->notes)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('unit_kerja_id')
                    ->label('Unit Kerja')
                    ->options(UnitKerja::pluck('unit_name', 'id')->toArray())
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('compliance_status')
                    ->label('Status Kepatuhan')
                    ->options([
                        1 => 'Patuh',
                        0 => 'Tidak Patuh',
                    ]),

                Filter::make('report_date')
                    ->schema([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('report_date', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('report_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalHeading(fn($record) => 'Detail Laporan Harian - ' . $record->report_date->format('d/m/Y'))
                    ->modalWidth('5xl')
                    ->schema(
                        fn(Schema $schema): Schema => $schema
                            ->components([
                                Section::make('Informasi Laporan')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('report_date')
                                                    ->label('Tanggal Laporan')
                                                    ->date('d/m/Y'),
                                                TextEntry::make('unitKerja.unit_name')
                                                    ->label('Unit Kerja'),
                                                TextEntry::make('submittedBy.name')
                                                    ->label('Diinput Oleh'),
                                                TextEntry::make('total_score')
                                                    ->label('Skor Kepatuhan')
                                                    ->formatStateUsing(fn($state) => number_format($state, 1) . '%')
                                                    ->color(fn($state) => $this->getScoreColor($state)),
                                                TextEntry::make('compliance_status')
                                                    ->label('Status Kepatuhan')
                                                    ->formatStateUsing(fn($state) => $state ? 'Patuh' : 'Tidak Patuh')
                                                    ->color(fn($state) => $state ? 'success' : 'danger'),
                                                TextEntry::make('created_at')
                                                    ->label('Waktu Input')
                                                    ->dateTime('d/m/Y H:i'),
                                            ])
                                    ]),

                                Section::make('Catatan')
                                    ->schema([
                                        TextEntry::make('notes')
                                            ->label('')
                                            ->default('Tidak ada catatan')
                                    ])
                                    ->visible(fn($record) => !empty($record->notes)),

                                Section::make('Detail Jawaban')
                                    ->schema([
                                        TextEntry::make('field_responses')
                                            ->label('')
                                            ->formatStateUsing(function ($record) {
                                                $responses = $record->fieldResponses()
                                                    ->with(['formField.options'])
                                                    ->get();

                                                if ($responses->isEmpty()) {
                                                    return 'Tidak ada detail jawaban.';
                                                }

                                                $html = '<div class="space-y-4">';
                                                foreach ($responses as $response) {
                                                    $field = $response->formField;
                                                    $score = number_format($response->compliance_score * 100, 1);
                                                    $scoreColor = $response->compliance_score >= 1 ? 'text-green-600' : 'text-red-600';

                                                    $html .= '<div class="border-l-4 border-gray-200 pl-4">';
                                                    $html .= '<h4 class="font-semibold text-sm text-gray-900">' . htmlspecialchars($field->field_label) . '</h4>';
                                                    $html .= '<p class="text-xs text-gray-600 mb-2">' . htmlspecialchars($field->field_description ?? '') . '</p>';

                                                    // Show response value
                                                    $fieldValue = $response->field_value;
                                                    if (is_array($fieldValue)) {
                                                        $displayValues = [];
                                                        foreach ($fieldValue as $value) {
                                                            $option = $field->options->where('option_value', $value)->first();
                                                            $displayValues[] = $option ? $option->option_text : $value;
                                                        }
                                                        $displayText = implode(', ', $displayValues);
                                                    } else {
                                                        $option = $field->options->where('option_value', $fieldValue)->first();
                                                        $displayText = $option ? $option->option_text : $fieldValue;
                                                    }

                                                    $html .= '<p class="text-sm"><strong>Jawaban:</strong> ' . htmlspecialchars($displayText) . '</p>';
                                                    $html .= '<p class="text-sm"><strong>Skor:</strong> <span class="' . $scoreColor . '">' . $score . '%</span></p>';

                                                    if ($response->validation_message) {
                                                        $html .= '<p class="text-sm"><strong>Catatan:</strong> ' . htmlspecialchars($response->validation_message) . '</p>';
                                                    }
                                                    $html .= '</div>';
                                                }
                                                $html .= '</div>';

                                                return $html;
                                            })
                                            ->html()
                                    ])
                            ])
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->url(fn($record) => "#") // TODO: implement edit functionality
                    ->visible(fn($record) => $this->canEditReport($record)),
            ])
            ->defaultSort('report_date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery()
    {
        $repo = app(DailyReportResponseRepositoryInterface::class);
        return $repo->getQueryForProfile($this->record->id);
    }

    protected function getScoreColor($score): string
    {
        if ($score >= 90) return 'success';
        if ($score >= 75) return 'warning';
        return 'danger';
    }

    protected function canEditReport($record): bool
    {
        // Allow edit within 24 hours of creation
        return $record->created_at->diffInHours(now()) <= 24;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_report')
                ->label('Tambah Laporan Harian')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn() => "#") // TODO: implement create functionality
                ->visible(fn() => $this->record->formTemplates()->exists()),

            Action::make('export')
                ->label('Export Data')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action('exportData'),
        ];
    }

    public function exportData()
    {
        // TODO: Implement export functionality
        $this->notify('success', 'Export akan segera tersedia');
    }


    public function getTitle(): string
    {
        return 'Laporan Harian';
    }

    public function getHeading(): string
    {
        return 'Laporan Harian - ' . ($this->record->version ?? 'Profil IMUT');
    }

    public function getSubheading(): ?string
    {
        $totalReports = $this->getTableQuery()->count();
        $complianceRate = $this->getTableQuery()->where('compliance_status', true)->count();
        $percentage = $totalReports > 0 ? round(($complianceRate / $totalReports) * 100, 1) : 0;

        return "Total {$totalReports} laporan | Tingkat kepatuhan: {$percentage}%";
    }
}
