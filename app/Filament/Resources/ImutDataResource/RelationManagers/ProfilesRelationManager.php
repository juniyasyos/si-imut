<?php

namespace App\Filament\Resources\ImutDataResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ImutProfile;
use Illuminate\Support\Facades\Gate;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ImutDataResource;
use Filament\Actions\ActionGroup;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{Action, ActionGroup as ActionsActionGroup, CreateAction, EditAction, DeleteAction, DeleteBulkAction, BulkActionGroup};
use Illuminate\Support\Facades\Auth;

class ProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'profiles';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version')
            ->defaultPaginationPageOption(5)
            ->modifyQueryUsing(fn($query) => $query->with(['formTemplates', 'imutData']))
            ->poll('30s')
            ->columns([
                TextColumn::make('version')
                    ->label('Versi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('indicator_type')
                    ->label('Tipe Indikator')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'process' => 'info',
                        'output' => 'warning',
                        'outcome' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('responsible_person')
                    ->label('Penanggung Jawab')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('valid_period')
                    ->label('Periode Berlaku')
                    ->state(function ($record) {
                        if (!$record->valid_from || !$record->valid_until) return '-';

                        $from = $record->valid_from;
                        $until = $record->valid_until;

                        if ($from->year === $until->year) {
                            return $from->translatedFormat('d M') . ' - ' . $until->translatedFormat('d M Y');
                        }

                        return $from->translatedFormat('d M Y') . ' - ' . $until->translatedFormat('d M Y');
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('form_template_status')
                    ->label('Form Template')
                    ->state(function ($record) {
                        $count = $record->formTemplates()->count();

                        return $count > 0
                            ? "{$count} Template"
                            : 'Belum Ada';
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return $state === 'Belum Ada'
                            ? 'warning'
                            : 'success';
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Data')
                    ->icon('heroicon-m-plus')
                    ->visible(function ($livewire) {
                        $owner = $livewire->ownerRecord;
                        return (Auth::user()?->can('create_imut::profile') && $owner->created_by === Auth::id()) || Auth::user()?->can('force_editable_imut::profile');
                    })
                    ->url(fn($livewire) => ImutDataResource::getUrl('create-profile', [
                        'imutDataSlug' => $livewire->ownerRecord->slug,
                    ])),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                DeleteAction::make()
                    ->visible(function (Model $record) {
                        return Auth::user()?->can('delete_imut::profile') && $record->imutData->created_by === Auth::id();
                    }),
                RestoreAction::make()->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
                ForceDeleteAction::make()->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
                ActionsActionGroup::make([
                    Action::make('edit')
                        ->label(fn($record) => (
                            $record && $record->created_by !== Auth::id() && !Auth::user()->can('force_editable_imut::profile')
                        ) ? 'Lihat' : 'Ubah')
                        ->icon(fn($record) => (
                            $record && $record->created_by !== Auth::id() && !Auth::user()->can('force_editable_imut::profile')
                        ) ? 'heroicon-o-eye' : 'heroicon-o-pencil-square')
                        ->visible(fn($record) => !is_null($record))
                        ->url(fn($record, $livewire) => ImutDataResource::getUrl('edit-profile', [
                            'imutDataSlug' => $livewire->ownerRecord->slug,
                            'record' => $record->slug,
                        ])),
                    \Filament\Tables\Actions\ReplicateAction::make()
                        ->using(function (Model $record) {
                            // Load relationships needed for replication
                            $record->load('formTemplates.formFields.options');

                            $newRecord = $record->replicate();
                            $originalVersion = $record->version;

                            $newVersion = "Copy dari $originalVersion";
                            $newRecord->version = $newVersion;

                            $slugBase = \Illuminate\Support\Str::slug($newVersion); // slugify version
                            $uuid = \Illuminate\Support\Str::uuid()->toString();

                            $newRecord->slug = "{$slugBase}-{$uuid}";

                            // Set new period to avoid overlap: start from today for 1 year
                            $newRecord->valid_from = now();
                            $newRecord->valid_until = now()->addYear();

                            // Ensure ID is null for new record
                            $newRecord->id = null;
                            $newRecord->save();

                            // Replicate form templates
                            $record->formTemplates->each(function ($template) use ($newRecord) {
                                $newTemplate = $template->replicate();
                                $newTemplate->imut_profile_id = $newRecord->id;
                                $newTemplate->save();

                                // Replicate form fields
                                $template->formFields->each(function ($field) use ($newTemplate) {
                                    $newField = $field->replicate();
                                    $newField->form_template_id = $newTemplate->id;
                                    $newField->save();

                                    // Replicate field options if they exist
                                    if ($field->relationLoaded('options')) {
                                        $field->options->each(function ($option) use ($newField) {
                                            $newOption = $option->replicate();
                                            $newOption->enhanced_form_field_id = $newField->id;
                                            $newOption->save();
                                        });
                                    }
                                });
                            });

                            return $newRecord;
                        })
                        ->visible(fn(?Model $record) => $record->imutData->created_by === Auth::id())
                        ->successNotificationTitle('Imut Profile successfully replicated'),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->tooltip('Lainnya')
                    ->button()
                    ->label('Aksi'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(
                            (Auth::user()?->can('delete_imut::profile'))
                                || Auth::user()?->can('force_editable_imut::profile')
                        ),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                ]),
            ])
            ->paginated(true)
            ->defaultPaginationPageOption(5);
    }
}
