<?php

namespace App\Filament\Resources\FolderCustomResource\Pages;

use App\Filament\Resources\FolderCustomResource;
use Filament\Actions;
use Illuminate\Support\Facades\Gate;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages\ListFolders;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class ListFoldersCustom extends ListFolders
{
    protected static string $resource = FolderCustomResource::class;

    public function mount(): void
    {
        $user = auth()->user();

        // Jika user hanya bisa view by unit kerja, dan memiliki tepat satu folder
        if (!$user->can('view_all_folder::custom') && $user->can('view_by_unit_kerja_folder::custom')) {
            $unitKerjas = $user->unitKerjas
                ->pluck('unit_name')
                ->map(fn($name) => \Illuminate\Support\Str::slug($name))
                ->toArray();

            $folders = Folder::whereNull('parent_id')->whereIn('collection', $unitKerjas)->get();

            if ($folders->count() === 1) {
                // Redirect langsung ke view folder
                redirect()->route('filament.resources.folder-custom.view', ['folder' => $folders->first()->id]);
            }
        }
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user->can('view_all_folder::custom') || $user->can('view_by_unit_kerja_folder::custom');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-m-plus')
                ->visible(fn() => Gate::any(['create_folder::custom'])),
        ];
    }

    // protected function getHeaderActions(): array
    // {
    //     dd([
    //         'user_permissions' => \Illuminate\Support\Facades\Auth::user()?->getAllPermissions()->pluck('name'),
    //         'all_permissions' => \Spatie\Permission\Models\Permission::all()->pluck('name'),
    //     ]);

    //     return [
    //         Actions\CreateAction::make()
    //             ->visible(fn () => Gate::any(['create_folder'])),
    //     ];
    // }

    public function getBreadcrumbs(): array
    {
        return [
            url('/') => 'Dashboard',
            'folders',
        ];
    }
}
