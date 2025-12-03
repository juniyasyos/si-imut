<?php

namespace App\Filament\Pages;

use App\Models\DailyReportEntry;
use App\Models\FormHeader;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class EditDailyReportEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.edit-daily-report-entry';

    protected static bool $shouldRegisterNavigation = false;

    public ?DailyReportEntry $entry = null;
    public ?FormHeader $formHeader = null;

    public ?array $data = [];

    public function mount(): void
    {
        $entryId = request()->query('entry');

        if (!$entryId) {
            abort(404, 'Entry parameter required');
        }

        $user = Auth::user();
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        $this->entry = DailyReportEntry::with(['formHeader.imutdata', 'formHeader.formFields'])
            ->whereIn('unit_kerja_id', $unitKerjaIds)
            ->findOrFail($entryId);
        $this->formHeader = $this->entry->formHeader;

        $this->form->fill([
            'report_date' => $this->entry->report_date,
            'responses' => $this->entry->responses ?? [],
        ]);
    }

    public function form(Form $form): Form
    {
        $formFields = [];

        foreach ($this->formHeader->formFields as $field) {
            $component = match ($field->type) {
                'text' => TextInput::make("responses.{$field->key}")
                    ->label($field->label)
                    ->required($field->is_required)
                    ->placeholder('Masukkan ' . strtolower($field->label))
                    ->helperText($field->description)
                    ->maxLength(255),

                'textarea' => Textarea::make("responses.{$field->key}")
                    ->label($field->label)
                    ->required($field->is_required)
                    ->placeholder('Masukkan ' . strtolower($field->label))
                    ->helperText($field->description)
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                'number' => TextInput::make("responses.{$field->key}")
                    ->label($field->label)
                    ->numeric()
                    ->required($field->is_required)
                    ->placeholder('0')
                    ->helperText($field->description)
                    ->minValue(0)
                    ->step(1)
                    ->prefix('📊'),

                'date' => DatePicker::make("responses.{$field->key}")
                    ->label($field->label)
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required($field->is_required)
                    ->helperText($field->description)
                    ->maxDate(now())
                    ->prefix('📅'),

                'bool' => Checkbox::make("responses.{$field->key}")
                    ->label($field->label)
                    ->required($field->is_required)
                    ->helperText($field->description)
                    ->inline(false)
                    ->columnSpanFull(),

                'select' => Select::make("responses.{$field->key}")
                    ->label($field->label)
                    ->options(is_array($field->options) ? array_combine($field->options, $field->options) : [])
                    ->required($field->is_required)
                    ->helperText($field->description)
                    ->searchable()
                    ->placeholder('Pilih ' . strtolower($field->label)),

                'radio' => Radio::make("responses.{$field->key}")
                    ->label($field->label)
                    ->options(is_array($field->options) ? array_combine($field->options, $field->options) : [])
                    ->required($field->is_required)
                    ->helperText($field->description)
                    ->inline()
                    ->columnSpanFull(),

                'checkbox' => CheckboxList::make("responses.{$field->key}")
                    ->label($field->label)
                    ->options(is_array($field->options) ? array_combine($field->options, $field->options) : [])
                    ->required($field->is_required)
                    ->helperText($field->description)
                    ->columns(2)
                    ->columnSpanFull(),

                default => TextInput::make("responses.{$field->key}")
                    ->label($field->label)
                    ->required($field->is_required)
                    ->helperText($field->description)
                    ->placeholder('Masukkan ' . strtolower($field->label)),
            };

            $formFields[] = $component;
        }

        $schema = [
            Section::make('📋 Informasi Waktu')
                ->description('Tentukan tanggal laporan yang akan diupdate')
                ->schema([
                    DatePicker::make('report_date')
                        ->label('Tanggal Laporan')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(now())
                        ->minDate(now()->subDays(6))
                        ->helperText('💡 Data dapat diinput maksimal 6 hari yang lalu')
                        ->default(now()->format('Y-m-d'))
                        ->columnSpanFull(),
                ])
                ->columnSpan('full')
                ->collapsible(),

            Section::make('📝 Data Laporan')
                ->description('Edit data laporan sesuai dengan indikator mutu')
                ->schema($formFields)
                ->columns(2)
                ->columnSpan('full'),
        ];

        return $form
            ->schema($schema)
            ->columns(1);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->entry->update([
            'report_date' => $data['report_date'],
            'responses' => $data['responses'] ?? [],
        ]);

        Notification::make()
            ->success()
            ->title('Laporan berhasil diupdate')
            ->send();

        $period = Carbon::parse($data['report_date'])->format('Y-m');

        $this->redirect(route('filament.admin.pages.daily-report-entries') . '?indicator=' . $this->formHeader->id . '&period=' . $period);
    }

    public function getTitle(): string
    {
        return 'Edit Laporan - ' . ($this->formHeader?->imutdata->title ?? '');
    }

    public function getBreadcrumbs(): array
    {
        $period = Carbon::parse($this->entry->report_date)->format('Y-m');

        return [
            route('filament.admin.pages.daily-report-dashboard') => 'Laporan Harian',
            route('filament.admin.pages.daily-report-periods') . '?indicator=' . $this->formHeader->id => $this->formHeader?->imutdata->title,
            route('filament.admin.pages.daily-report-entries') . '?indicator=' . $this->formHeader->id . '&period=' . $period => $period,
            '#' => 'Edit Laporan',
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->hasRole('Unit Kerja') && $user->unitKerjas()->exists();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan')
                ->action('save')
                ->color('primary'),

            \Filament\Actions\Action::make('cancel')
                ->label('Batal')
                ->url(fn() => route('filament.admin.pages.daily-report-entries') . '?indicator=' . $this->formHeader->id . '&period=' . Carbon::parse($this->entry->report_date)->format('Y-m'))
                ->color('gray'),
        ];
    }
}
