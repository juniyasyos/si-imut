<?php

namespace App\Modules\FormEngine\Services;

use App\Services\Authorization\ImutDataPermissionService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImutDataFormBuilderService
{
    public function __construct(
        private readonly ImutDataPermissionService $permissionService
    ) {}

    /**
     * Build unit kerja information section
     */
    public function buildUnitKerjaInfoSection(): Section
    {
        return Section::make('Informasi Unit Kerja')
            ->visible(fn() => $this->permissionService->canViewUnitKerjaInfo())
            ->disabled()
            ->schema([
                Placeholder::make('unitKerjaInfo')
                    ->label('Unit Kerja Pengguna')
                    ->content(fn() => $this->permissionService->getUserUnitKerjaInfo())
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Build main profile form tab
     */
    public function buildMainProfileTab(): Tab
    {
        return Tab::make('📋 Form Profil Indikator')
            ->schema([
                Grid::make(2)->schema([
                    $this->buildTitleField(),
                    $this->buildSlugField(),
                    $this->buildCategorySelect(),
                    // Add more fields as needed
                ])
            ]);
    }

    /**
     * Build title input field with permissions
     */
    public function buildTitleField(): TextInput
    {
        return TextInput::make('title')
            ->label(__('filament-forms::imut-data.fields.title'))
            ->placeholder(__('filament-forms::imut-data.form.main.title_placeholder'))
            ->helperText(__('filament-forms::imut-data.form.main.helper_text'))
            ->prefixIcon('heroicon-o-pencil-square')
            ->required()
            ->readOnly(fn(?Model $record) =>
                ($record && $record->created_by !== Auth::id()) &&
                !$this->permissionService->canEditImutData($record->created_by ?? null)
            )
            ->columnSpan(2)
            ->unique('imut_data', 'title', ignoreRecord: true)
            ->maxLength(255);
    }

    /**
     * Build slug field (read-only)
     */
    public function buildSlugField(): TextInput
    {
        return TextInput::make('slug')
            ->label(__('filament-forms::imut-data.fields.slug'))
            ->readOnly()
            ->disabled()
            ->extraAttributes(['class' => 'bg-gray-100 text-gray-500'])
            ->visibleOn('edit')
            ->columnSpan(1);
    }

    /**
     * Build category select with filtered options
     */
    public function buildCategorySelect(): Select
    {
        return Select::make('imut_kategori_id')
            ->label(__('Kategori'))
            ->options(fn() => $this->permissionService->getAvailableImutCategories()->pluck('category_name', 'id'))
            ->searchable()
            ->required()
            ->columnSpan(1)
            ->reactive()
            ->afterStateUpdated(function ($state, callable $set) {
                // Reset dependent fields when category changes
                $set('imut_profil_id', null);
            });
    }

    /**
     * Build complete form schema
     */
    public function buildCompleteFormSchema(): array
    {
        $schema = [];

        // Add unit kerja info section if user has permission
        if ($this->permissionService->canViewUnitKerjaInfo()) {
            $schema[] = $this->buildUnitKerjaInfoSection();
        }

        // Add main tabs
        $schema[] = Tabs::make('')
            ->columnSpan(['lg' => 2])
            ->tabs([
                $this->buildMainProfileTab(),
                // Add more tabs as needed
            ]);

        return $schema;
    }

    /**
     * Build validation rules based on permissions
     */
    public function getValidationRules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'imut_kategori_id' => ['required', 'exists:imut_kategori,id'],
        ];

        // Add conditional rules based on permissions
        if ($this->permissionService->canCreateImutProfile()) {
            $rules['description'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Get form state based on user permissions
     */
    public function getFormState(?Model $record = null): array
    {
        $permissions = $this->permissionService->getUserPermissions(
            recordCreatedBy: $record->created_by ?? null
        );

        return [
            'readonly_mode' => !$permissions['can_edit'],
            'show_advanced_fields' => $permissions['has_force_edit'],
            'available_categories' => $this->permissionService->getAvailableImutCategories(),
            'permissions' => $permissions,
        ];
    }

    /**
     * Process form data before save
     */
    public function processFormData(array $data, ?Model $record = null): array
    {
        // Add created_by for new records
        if (!$record && Auth::id()) {
            $data['created_by'] = Auth::id();
        }

        // Process slug if title changed
        if (isset($data['title']) && (!$record || $record->title !== $data['title'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }

        return $data;
    }

    /**
     * Get form field configuration based on record and permissions
     */
    public function getFieldConfiguration(?Model $record = null): array
    {
        $permissions = $this->permissionService->getUserPermissions(
            recordCreatedBy: $record->created_by ?? null
        );

        return [
            'title' => [
                'readonly' => !$permissions['can_edit'],
                'required' => true,
                'visible' => true,
            ],
            'slug' => [
                'readonly' => true,
                'visible' => $record !== null, // Only show on edit
            ],
            'category' => [
                'readonly' => !$permissions['can_edit'],
                'required' => true,
                'options' => $this->permissionService->getAvailableImutCategories(),
            ],
        ];
    }
}
