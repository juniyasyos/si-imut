<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use App\Models\UnitKerja;
use App\Repositories\Interfaces\UnitKerjaFolderRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class UnitKerjaObserver
{
    protected UnitKerjaFolderRepositoryInterface $repository;

    public function __construct(UnitKerjaFolderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function created(UnitKerja $unitKerja): void
    {
        try {
            // Additional safety check to prevent duplicates
            $collection = Str::slug($unitKerja->unit_name);
            $existingFolder = Folder::where('collection', $collection)
                ->whereNull('parent_id')
                ->exists();

            if ($existingFolder) {
                Log::warning("⚠️ Folder sudah ada untuk UnitKerja ID {$unitKerja->id} - {$unitKerja->unit_name}. Skip pembuatan untuk mencegah duplikat.");
                return;
            }

            $this->repository->createFolder($unitKerja);
            Log::info("✅ UnitKerja berhasil dibuat: ID {$unitKerja->id} - {$unitKerja->unit_name}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal membuat folder untuk UnitKerja ID {$unitKerja->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function updated(UnitKerja $unitKerja): void
    {
        try {
            $this->repository->updateFolder($unitKerja);
            Log::info("✏️ UnitKerja diperbarui: ID {$unitKerja->id}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal memperbarui folder untuk UnitKerja ID {$unitKerja->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function deleted(UnitKerja $unitKerja): void
    {
        try {
            $this->repository->markFolderAsDeleted($unitKerja);
            Log::warning("⚠️ UnitKerja dihapus (soft delete): ID {$unitKerja->id}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal menandai folder sebagai dihapus untuk UnitKerja ID {$unitKerja->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function forceDeleted(UnitKerja $unitKerja): void
    {
        Log::info("🗑️ UnitKerja dihapus permanen: ID {$unitKerja->id}. Folder tetap dipertahankan.");
    }

    public function restored(UnitKerja $unitKerja): void
    {
        try {
            $this->repository->restoreFolder($unitKerja);
            Log::info("♻️ UnitKerja dipulihkan: ID {$unitKerja->id} - {$unitKerja->unit_name}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal memulihkan folder untuk UnitKerja ID {$unitKerja->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
