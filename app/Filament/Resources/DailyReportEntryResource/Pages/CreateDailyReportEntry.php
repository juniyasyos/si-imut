<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use Carbon\Carbon;
use Exception;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormTemplate;
use App\Services\DailyReport\DailyReportBuildService;
use App\Services\DailyReport\DailyReportAuthorizationService;
use App\Services\DynamicForm\DynamicFormService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateDailyReportEntry extends CreateRecord
{
    protected static string $resource = DailyReportEntryResource::class;

    protected static bool $canCreateAnother = false;

    protected string $view = 'filament.pages.create-daily-report-entry';

    private DailyReportAuthorizationService $creationService;
    private DailyReportBuildService $buildService;

    public ?FormTemplate $formTemplate = null;
    public ?string $originalIndicatorId = null;
    public ?string $originalDate = null;

    public function __construct()
    {
        $this->creationService = app(DailyReportAuthorizationService::class);
        $this->buildService = app(DailyReportBuildService::class);
    }

    /**
     * Authorize access to create page - delegates to service
     */
    protected function authorizeAccess(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $indicatorId = request()->query('indicator');
        if (!$indicatorId) {
            return; // Will be redirected in mount()
        }

        // Use service to authorize access
        if (!$this->creationService->authorizeUserAccess($user, (int) $indicatorId)) {
            abort(403);
        }
    }

    /**
     * Mount component - resolves template and initializes form
     */
    public function mount(): void
    {
        $user = Auth::user();
        $indicatorId = request()->query('indicator');
        $date = request()->query('date');

        // Store original parameters for redirect
        $this->originalIndicatorId = $indicatorId;
        $this->originalDate = $date ?? now()->format('Y-m-d');

        if (!$indicatorId) {
            Notification::make()
                ->title('Parameter Tidak Lengkap')
                ->body('Silakan pilih indikator terlebih dahulu dari halaman daftar laporan.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        // Find requested template
        $requestedTemplate = FormTemplate::find((int) $indicatorId);

        if (!$requestedTemplate || !$requestedTemplate->imutProfile) {
            Notification::make()
                ->title('Form Template Tidak Ditemukan')
                ->body('Form template tidak ditemukan atau sudah dihapus.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        // Resolve template valid for report date using service
        $this->formTemplate = $this->creationService->resolveTemplateForDate($requestedTemplate, $this->originalDate);

        if (!$this->formTemplate) {
            Notification::make()
                ->title('Template Tidak Tersedia')
                ->body('Tidak ada template yang valid untuk tanggal laporan yang dipilih.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        // Load required relationships
        $this->formTemplate->load(['formFields.options', 'imutProfile.imutData.unitKerja']);

        // Authorize access using service
        if (!$this->creationService->authorizeUserAccess($user, (int) $this->formTemplate->id)) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki akses untuk membuat laporan untuk indikator ini.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        parent::mount();

        // Initialize form data
        $this->data = DynamicFormService::initializeFormData($this->formTemplate);
        $this->form->fill($this->data);
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
        $indicatorId = request()->query('indicator');

        if ($indicatorId) {
            $formTemplate = FormTemplate::with('imutProfile')->find($indicatorId);
            if ($formTemplate && $formTemplate->imutProfile && $formTemplate->imutProfile->title) {
                return $formTemplate->imutProfile->title;
            }
        }

        return 'Laporan Harian';
    }

    /**
     * Get form description
     */
    public function getFormDescription(): ?string
    {
        if ($this->formTemplate) {
            return $this->formTemplate->description;
        }

        return null;
    }

    /**
     * Get formatted date
     */
    public function getFormattedDate(): string
    {
        $date = request()->query('date');

        if ($date) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date)->format('d F Y');
            } catch (Exception $e) {
                return now()->format('d F Y');
            }
        }

        return now()->format('d F Y');
    }

    /**
     * Get category badge color based on template
     */
    public function getCategoryBadgeColor(): string
    {
        if ($this->formTemplate && $this->formTemplate->imutProfile) {
            // Generate consistent color based on title
            $colors = ['blue', 'green', 'purple', 'orange', 'red', 'indigo', 'pink'];
            $index = abs(crc32($this->formTemplate->imutProfile->title)) % count($colors);
            return $colors[$index];
        }

        return 'gray';
    }

    /**
     * Configure the form
     */
    public function form(Schema $schema): Schema
    {
        if (!$this->formTemplate) {
            return $schema->components([]);
        }

        return $schema
            ->components(DynamicFormService::buildFormSchema($this->formTemplate, true, true))
            ->statePath('data')
            ->live();
    }

    /**
     * Mutate form data before creating record - delegates to service
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            $user = Auth::user();

            if (!$user) {
                throw new Exception('Anda harus login terlebih dahulu');
            }

            if (!$user->id) {
                throw new Exception('User ID tidak valid. Silakan logout dan login kembali.');
            }

            if (!$this->formTemplate) {
                throw new Exception('Form template tidak ditemukan');
            }

            // Log user info for debugging
            Log::info('Creating report for user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'template_id' => $this->formTemplate->id,
                'date' => $this->originalDate
            ]);

            $unitKerja = $user->unitKerjas()->first();

            if (! $unitKerja) {
                throw new Exception('User tidak terdaftar di unit kerja mana pun');
            }

            $this->record = $this->buildService->create(
                $this->formTemplate,
                $data,
                $unitKerja,
                $user,
                Carbon::createFromFormat('Y-m-d', $this->originalDate)
            );

            return [];
        } catch (Exception $e) {
            Log::error('Error in mutateFormDataBeforeCreate', [
                'user_id' => Auth::id(),
                'template_id' => $this->formTemplate?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Gagal membuat laporan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();

            return []; // Unreachable, but satisfies return type requirement
        }
    }



    /**
     * Override record creation to prevent Filament from creating a second record
     */
    protected function handleRecordCreation(array $data): Model
    {
        // The record is already created in mutateFormDataBeforeCreate
        // So we just return it here
        if ($this->record && $this->record->id) {
            return $this->record;
        }

        // Fallback: create using parent method (should not reach here)
        return parent::handleRecordCreation($data);
    }

    /**
     * Override redirect after successful creation
     */
    protected function getRedirectUrl(): string
    {
        $params = [];

        // Use stored parameters instead of request()->query()
        if ($this->originalIndicatorId) {
            $params['indicator_id'] = $this->originalIndicatorId;
        }

        if ($this->originalDate) {
            $params['date'] = $this->originalDate;
        }

        $url = $this->getResource()::getUrl('index');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Get success notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan berhasil dibuat')
            ->body('Laporan harian telah berhasil disimpan');
    }
}
