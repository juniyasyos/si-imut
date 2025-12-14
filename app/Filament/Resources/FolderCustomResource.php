<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Tables;
use Filament\Tables\Table;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource as BaseFolderResource;

class FolderCustomResource extends BaseFolderResource implements HasShieldPermissions
{
    use HasActiveIcon;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'view_all',
            'view_by_unit_kerja',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    /**
     * Override slug resource secara statik.
     */
    public static function getSlug(): string
    {
        return config('filament-media-manager.slug_folder', 'folder-custom');
    }

    /**
     * Konfigurasi tabel resource, termasuk scoping berdasarkan unit kerja dan request parameter.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $user = \Illuminate\Support\Facades\Auth::user();
                $query = Folder::query();

                // IMPORTANT: Filter hanya root folders (parent_id = null)
                $query->whereNull('parent_id');

                // Filter berdasarkan permission
                if (! $user->can('view_all_folder::custom') && $user->can('view_by_unit_kerja_folder::custom')) {
                    $unitKerjas = $user->unitKerjas
                        ->pluck('unit_name')
                        ->map(fn($name) => \Illuminate\Support\Str::slug($name))
                        ->toArray();

                    // Filter by collection karena collection pakai slug
                    $query->whereIn('collection', $unitKerjas);
                }

                if (! $user->can('view_all_folder::custom') && ! $user->can('view_by_unit_kerja_folder::custom')) {
                    $query->whereRaw('0 = 1');
                }

                return $query;
            })

            ->content(fn() => view('filament-media-manager::pages.folders'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('filament-media-manager::messages.folders.columns.name'))
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions(['12', '24', '48', '96'])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\FolderCustomResource\Pages\ListFoldersCustom::route('/'),
            'view' => \Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages\ViewFolder::route('/{folder}'),
            'media' => \App\Filament\Resources\MediaCustomResource\Pages\ListMediaCustom::route('/{folder}/media'),
        ];
    }
}
