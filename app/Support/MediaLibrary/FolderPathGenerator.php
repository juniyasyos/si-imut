<?php

namespace App\Support\MediaLibrary;

use Juniyasyos\FilamentMediaManager\Models\Folder;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class FolderPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    /**
     * Get base path based on collection name (folder structure)
     */
    protected function getBasePath(Media $media): string
    {
        $collection = $media->collection_name;

        // Jika collection kosong, gunakan ID media
        if (empty($collection)) {
            return (string) $media->id;
        }

        // PRIORITY: Cek directory dari custom properties untuk periode folder
        $customDirectory = $media->getCustomProperty('directory');
        if ($customDirectory) {
            return $customDirectory;
        }

        // Cek apakah media attached ke Folder model
        if ($media->model_type === 'Juniyasyos\FilamentMediaManager\Models\Folder' && $media->model_id) {
            return Cache::remember(
                CacheKey::folderPathByFolderId($media->model_id),
                now()->addHours(2),
                fn() => $this->resolveFolderPathFromFolderId($media->model_id)
            );
        }

        return Cache::remember(
            CacheKey::folderPathByCollection($collection),
            now()->addHours(2),
            fn() => $this->resolveFolderPathByCollection($collection) ?? $collection
        );
    }

    private function resolveFolderPathFromFolderId(int $folderId): string
    {
        $folder = Folder::find($folderId);

        if (! $folder) {
            return (string) $folderId;
        }

        return $this->buildFolderPath($folder);
    }

    private function resolveFolderPathByCollection(string $collection): ?string
    {
        $folder = Folder::where('collection', $collection)->first();

        if (! $folder) {
            return null;
        }

        return $this->buildFolderPath($folder);
    }

    private function buildFolderPath(Folder $folder): string
    {
        $path = [];
        $current = $folder;

        while ($current) {
            array_unshift($path, $current->collection);
            $current = $current->parent;
        }

        return implode('/', $path);
    }
}
