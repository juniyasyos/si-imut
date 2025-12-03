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

class CreateDailyReportEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.create-daily-report-entry';

    protected static bool $shouldRegisterNavigation = false;

    public ?FormHeader $formHeader = null;
    public ?string $period = null;

    public ?array $data = [];

    public function mount(): void
    {
        $indicatorId = request()->query('indicator');
        $period = request()->query('period');

        if (!$indicatorId) {
            abort(404, 'Indicator parameter required');
        }

        $this->formHeader = FormHeader::with(['imutdata', 'formFields'])->findOrFail($indicatorId);
        $this->period = $period;
        $this->form->fill([
            'report_date' => now()->format('Y-m-d'),
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
                    ->options(collect($field->options ?? [])->pluck('label', 'value')->toArray())
                    ->required($field->is_required)
                    ->helperText($field->description)
                    ->searchable()
                    ->placeholder('Pilih ' . strtolower($field->label)),

                'radio' => Radio::make("responses.{$field->key}")
                    ->label($field->label)
                    ->options(collect($field->options ?? [])->pluck('label', 'value')->toArray())
                    ->required($field->is_required)
                    ->helperText($field->description)
                    ->inline()
                    ->columnSpanFull(),

                'checkbox' => CheckboxList::make("responses.{$field->key}")
                    ->label($field->label)
                    ->options(collect($field->options ?? [])->pluck('label', 'value')->toArray())
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
                ->description('Tentukan tanggal laporan yang akan diinput')
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
                ->description('Isi data laporan sesuai dengan indikator mutu')
                ->schema($formFields)
                ->columns(2)
                ->columnSpan('full'),
        ];

        return $form
            ->schema($schema)
            ->columns(1);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();
        $unitKerjaId = $user->unitKerjas()->first()->id ?? null;

        if (!$unitKerjaId) {
            Notification::make()
                ->danger()
                ->title('Error: Unit Kerja tidak ditemukan')
                ->send();
            return;
        }

        DailyReportEntry::create([
            'form_header_id' => $this->formHeader->id,
            'unit_kerja_id' => $unitKerjaId,
            'submitted_by' => Auth::id(),
            'report_date' => $data['report_date'],
            'entry_time' => now(),
            'responses' => $data['responses'] ?? [],
        ]);

        Notification::make()
            ->success()
            ->title('Laporan berhasil disimpan')
            ->send();

        $period = $this->period ?? Carbon::parse($data['report_date'])->format('Y-m');

        $this->redirect(route('filament.admin.pages.daily-report-entries') . '?indicator=' . $this->formHeader->id . '&period=' . $period);
    }

    public function getTitle(): string
    {
        return 'Tambah Laporan - ' . ($this->formHeader?->imutdata->title ?? '');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.daily-report-dashboard') => 'Laporan Harian',
            route('filament.admin.pages.daily-report-periods') . '?indicator=' . $this->formHeader->id => $this->formHeader?->imutdata->title,
            '#' => 'Tambah Laporan',
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
            \Filament\Actions\Action::make('create')
                ->label('Simpan')
                ->action('create')
                ->color('primary'),

            \Filament\Actions\Action::make('cancel')
                ->label('Batal')
                ->url(fn() => route('filament.admin.pages.daily-report-periods') . '?indicator=' . $this->formHeader->id)
                ->color('gray'),
        ];
    }
}
