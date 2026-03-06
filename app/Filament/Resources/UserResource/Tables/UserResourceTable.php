<?php

namespace App\Filament\Resources\UserResource\Tables;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Gate;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResourceTable
{
    public static function columns(): array
    {
        return [
            Split::make([
                ImageColumn::make('avatar_url')
                    ->searchable()
                    ->circular()
                    ->grow(false)
                    ->getStateUsing(fn($record) => $record->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                Stack::make([
                    TextColumn::make('name')
                        ->label(__('filament-forms::users.fields.name'))
                        ->searchable()
                        ->weight(FontWeight::Bold),

                    TextColumn::make('roles.label')
                        ->label(__('filament-forms::users.fields.roles'))
                        ->searchable()
                        ->icon('heroicon-o-shield-check')
                        ->grow(false)
                ])->alignStart()->space(1),
                Stack::make([
                    TextColumn::make('roles.name')
                        ->label(__('filament-forms::users.fields.roles'))
                        ->searchable()
                        ->icon('heroicon-o-document-text')
                        ->grow(false)
                        ->formatStateUsing(function ($record) {
                            $roles = $record->roles->pluck('name')->toArray();

                            // Jika user memiliki role pengumpul_data atau validator_pic, tampilkan unit kerja
                            if (in_array('pengumpul_data', $roles) || in_array('validator_pic', $roles)) {
                                $unitKerja = $record->unitKerjas->first();
                                // $roleLabel = in_array('pengumpul_data', $roles) ? 'Pengumpul Data' : 'Validator/PIC';
                                $unitName = $unitKerja ? $unitKerja->unit_name : 'Unit Tidak Ditemukan';
                                return "Unit Kerja : {$unitName}";
                            }

                            // Untuk admin dan tim_mutu, tampilkan label role
                            $roleLabels = [
                                'admin' => 'Administrator',
                                'tim_mutu' => 'Tim Mutu',
                            ];

                            $displayRoles = array_map(function ($role) use ($roleLabels) {
                                return $roleLabels[$role] ?? ucwords(str_replace('_', ' ', $role));
                            }, $roles);

                            return implode(', ', $displayRoles);
                        }),
                    TextColumn::make('nip')
                        ->label(__('filament-forms::users.fields.email'))
                        ->icon('heroicon-m-finger-print')
                        ->searchable()
                        ->copyable()
                        ->copyMessage('NIP berhasil disalin!')
                        ->copyMessageDuration(1500)
                        ->grow(false),
                ])->alignStart()->visibleFrom('lg')->space(1),
            ]),
        ];
    }

    public static function filters(): array
    {
        return [
            TrashedFilter::make()->default('with'),
            SelectFilter::make('roles')
                ->label(__('filament-forms::users.filters.roles'))
                ->relationship('roles', 'name')
                ->multiple()
                ->preload(),
        ];
    }

    public static function actions(): array
    {
        return [
            ActivityLogTimelineTableAction::make(__('filament-forms::users.actions.activities'))
                ->visible(fn() => Gate::allows('viewActivities', User::class)),

            Action::make(__('filament-forms::users.actions.set_role'))
                ->icon('heroicon-m-adjustments-vertical')
                ->form([
                    Select::make('role')
                        ->label(__('filament-forms::users.fields.roles'))
                        ->relationship('roles', 'label')
                        // ->multiple()
                        ->searchable()
                        ->preload()
                        ->optionsLimit(10)
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->label),
                ])
                ->visible(fn() => Gate::allows('setRole', User::class)),

            Impersonate::make()
                ->label(__('filament-forms::users.actions.impersonate'))
                ->visible(fn() => Gate::allows('impersonate', User::class)),

            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make()
                    ->visible(
                        fn($record) => Gate::allows('restore', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),

                ForceDeleteAction::make()
                    ->visible(
                        fn($record) => Gate::allows('forceDelete', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),
            ])->button()->label(__('filament-forms::users.actions.group'))
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->visible(fn() => Gate::allows('deleteAny', User::class)),
                RestoreBulkAction::make()
                    ->visible(fn() => Gate::allows('restoreAny', User::class)),
                ForceDeleteBulkAction::make()
                    ->visible(fn() => Gate::allows('forceDeleteAny', User::class)),
                ExportBulkAction::make()
                    ->exporter(UserExporter::class)
                    ->visible(fn() => Gate::allows('export', User::class)),
            ]),
        ];
    }
}
