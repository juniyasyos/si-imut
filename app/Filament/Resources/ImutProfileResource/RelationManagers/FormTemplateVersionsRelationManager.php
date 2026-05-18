<?php

namespace App\Filament\Resources\ImutProfileResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\FormTemplate;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ImutDataResource;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{Action, ActionGroup as ActionsActionGroup, CreateAction, EditAction, DeleteAction, DeleteBulkAction, BulkActionGroup};
use Illuminate\Support\Facades\Auth;
use App\Services\Form\FormTemplateVersionService;
use Filament\Notifications\Notification;

class FormTemplateVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formTemplateVersions';

    protected static ?string $title = 'Form Template Versions';

    protected static ?string $modelLabel = 'Form Template Version';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version')
            ->columns([
                TextColumn::make('version')
                    ->label('Version')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn($record): string => $record->is_active ? 'success' : 'gray'),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => 'Active',
                        'gray' => 'Inactive',
                    ]),

                // TextColumn::make('compliance_method')
                //     ->label('Compliance Method')
                //     ->badge()
                //     ->formatStateUsing(fn(string $state): string => match ($state) {
                //         'auto_calculate' => 'Auto Calculate',
                //         'manual' => 'Manual',
                //         'weighted' => 'Weighted',
                //         default => $state,
                //     })
                //     ->color(fn(string $state): string => match ($state) {
                //         'auto_calculate' => 'info',
                //         'manual' => 'warning',
                //         'weighted' => 'success',
                //         default => 'gray',
                //     }),

                TextColumn::make('valid_period')
                    ->label('Valid Period')
                    ->getStateUsing(function ($record) {
                        if ($record->valid_from && $record->valid_until) {

                            if ($record->valid_from->year === $record->valid_until->year) {
                                return $record->valid_from->translatedFormat('d M')
                                    . ' - '
                                    . $record->valid_until->translatedFormat('d M Y');
                            }

                            return $record->valid_from->translatedFormat('d M Y')
                                . ' - '
                                . $record->valid_until->translatedFormat('d M Y');
                        }

                        if ($record->valid_from) {
                            return $record->valid_from->translatedFormat('d M Y')
                                . ' - Present';
                        }

                        return 'Not Set';
                    })
                    ->sortable(['valid_from', 'valid_until']),

                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('manage_form_builder')
                    ->label('Manage Form Builder')
                    ->icon('heroicon-m-cog-8-tooth')
                    ->visible(function ($livewire) {
                        $profile = $livewire->ownerRecord;
                        return (Auth::user()?->can('create_form_template') && $profile->imutData->created_by === Auth::id())
                            || Auth::user()?->can('force_editable_imut::profile');
                    })
                    ->url(fn($livewire) => ImutDataResource::getUrl('manage-form-builder', [
                        'imutDataSlug' => $livewire->ownerRecord->imutData->slug,
                        'record' => $livewire->ownerRecord->slug,
                        'templateId' => $livewire->ownerRecord->activeFormTemplate?->id,
                    ])),

                Action::make('create_from_latest')
                    ->label('Create Version from Latest')
                    ->icon('heroicon-m-document-duplicate')
                    ->color('info')
                    ->visible(function ($livewire) {
                        $profile = $livewire->ownerRecord;
                        return $profile->latestFormTemplate &&
                            ((Auth::user()?->can('create_form_template') && $profile->imutData->created_by === Auth::id())
                                || Auth::user()?->can('force_editable_imut::profile'));
                    })
                    ->action(function ($livewire) {
                        $profile = $livewire->ownerRecord;
                        $latestTemplate = $profile->latestFormTemplate;

                        if ($latestTemplate) {
                            $versionService = new FormTemplateVersionService();
                            $newTemplate = $versionService->createNewVersion($latestTemplate, [
                                'title' => $latestTemplate->title . ' (New Version)',
                            ]);

                            Notification::make()
                                ->title('New version created successfully')
                                ->success()
                                ->body("Version {$newTemplate->version} has been created based on {$latestTemplate->version}")
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Create New Version')
                    ->modalDescription('This will create a new version based on your latest form template. You can then modify it as needed.')
                    ->modalSubmitActionLabel('Create Version'),
            ])
            ->actions([
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(FormTemplate $record): bool => !$record->is_active)
                    ->action(function (FormTemplate $record) {
                        $versionService = new FormTemplateVersionService();
                        $versionService->activateVersion($record);

                        Notification::make()
                            ->title('Template activated')
                            ->success()
                            ->body("Version {$record->version} is now active")
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Activate Template Version')
                    ->modalDescription('This will deactivate all other versions and make this version active. Are you sure?'),

                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn(FormTemplate $record): bool => $record->is_active)
                    ->action(function (FormTemplate $record) {
                        $versionService = new FormTemplateVersionService();
                        $versionService->deactivateVersion($record);

                        Notification::make()
                            ->title('Template deactivated')
                            ->success()
                            ->body("Version {$record->version} has been deactivated")
                            ->send();
                    })
                    ->requiresConfirmation(),

                DeleteAction::make()
                    ->visible(function (FormTemplate $record) {
                        return !$record->is_active &&
                            (Auth::user()?->can('delete_form_template') || Auth::user()?->can('force_editable_imut::profile'));
                    })
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this template version? This action cannot be undone.'),

                RestoreAction::make()
                    ->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),

                ForceDeleteAction::make()
                    ->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),

                ActionsActionGroup::make([
                    Action::make('edit')
                        ->label('Edit Template')
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn(FormTemplate $record, $livewire) => ImutDataResource::getUrl('manage-form-builder', [
                            'imutDataSlug' => $livewire->ownerRecord->imutData->slug,
                            'record' => $livewire->ownerRecord->slug,
                            'templateId' => $record->id,
                        ])),

                    Action::make('duplicate')
                        ->label('Create New Version')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (FormTemplate $record) {
                            $versionService = new FormTemplateVersionService();
                            $newTemplate = $versionService->createNewVersion($record);

                            Notification::make()
                                ->title('New version created')
                                ->success()
                                ->body("Version {$newTemplate->version} created from {$record->version}")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Create New Version')
                        ->modalDescription('This will create a copy of this template version that you can modify independently.'),

                    Action::make('view_fields')
                        ->label('View Fields')
                        ->icon('heroicon-o-list-bullet')
                        ->url(fn(FormTemplate $record, $livewire) => ImutDataResource::getUrl('manage-form-builder', [
                            'imutDataSlug' => $livewire->ownerRecord->imutData->slug,
                            'record' => $livewire->ownerRecord->slug,
                            'templateId' => $record->id,
                        ])),

                    Action::make('preview')
                        ->label('Preview Form')
                        ->icon('heroicon-o-eye')
                        ->url(fn(FormTemplate $record, $livewire) => ImutDataResource::getUrl('preview-form', [
                            'imutDataSlug' => $livewire->ownerRecord->imutData->slug,
                            'record' => $livewire->ownerRecord->slug,
                        ])),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->tooltip('More Actions')
                    ->button()
                    ->label('Actions'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(Auth::user()?->can('delete_form_template') || Auth::user()?->can('force_editable_imut::profile'))
                        ->deselectRecordsAfterCompletion(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(true)
            ->defaultPaginationPageOption(10);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('version')
                    ->label('Version')
                    ->required()
                    ->maxLength(50),

                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                // Add more form fields as needed
            ]);
    }
}
