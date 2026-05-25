<?php

namespace App\Filament\Resources\UserResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ExportBulkAction;
use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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

    public static function headerActions(): array
    {
        return [
            Action::make('exportUsersJson')
                ->label('Unduh JSON')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $relativePath = 'exports/users.json';
                    $absolutePath = storage_path('app/' . $relativePath);

                    Artisan::call('users:export-json', ['--path' => $relativePath]);

                    return response()->download(
                        $absolutePath,
                        'users.json',
                        ['Content-Type' => 'application/json']
                    );
                })
                ->visible(fn() => Gate::allows('export', User::class)),
        ];
    }

    public static function actions(): array
    {
        return [
            ActivityLogTimelineTableAction::make(__('filament-forms::users.actions.activities'))
                ->disabled()
                ->visible(fn() => Gate::allows('viewActivities', User::class)),

            Action::make(__('filament-forms::users.actions.set_role'))
                ->icon('heroicon-m-adjustments-vertical')
                ->schema([
                    Select::make('role')
                        ->label(__('filament-forms::users.fields.roles'))
                        ->options(function (): array {
                            return Cache::remember('users:set_role:options', now()->addMinutes(30), function () {
                                return Role::query()
                                    ->orderBy('name')
                                    ->pluck('label', 'id')
                                    ->toArray();
                            });
                        })
                        ->searchable()
                        ->optionsLimit(10),
                ])
                ->visible(fn() => !Gate::allows('setRole', User::class) || !(env('USE_SSO') || env('IAM_ENABLED')) || config('iam.role_sync_mode') === 'pull'),

            Impersonate::make()
                ->label(__('filament-forms::users.actions.impersonate'))
                ->hidden(
                    function () {
                        return !auth()->user()?->can('impersonate_user');
                    }
                ),

            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(config('iam.user_sync_from_iam_allow_create')),
                RestoreAction::make()
                    ->visible(
                        fn($record) => config('iam.user_sync_from_iam_allow_create') && Gate::allows('restore', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),

                ForceDeleteAction::make()
                    ->visible(
                        fn($record) => config('iam.user_sync_from_iam_allow_create') && Gate::allows('forceDelete', $record) &&
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
                    ->visible(fn() => config('iam.user_sync_from_iam_allow_create') && Gate::allows('deleteAny', User::class)),
                RestoreBulkAction::make()
                    ->visible(fn() => config('iam.user_sync_from_iam_allow_create') && Gate::allows('restoreAny', User::class)),
                ForceDeleteBulkAction::make()
                    ->visible(fn() => config('iam.user_sync_from_iam_allow_create') && Gate::allows('forceDeleteAny', User::class)),
                ExportBulkAction::make()
                    ->exporter(UserExporter::class)
                    ->visible(fn() => config('iam.user_sync_from_iam_allow_create') && Gate::allows('export', User::class)),
            ]),
        ];
    }
}
