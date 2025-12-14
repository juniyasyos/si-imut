<?php

namespace App\Repositories;

use App\Models\UnitKerja;
use App\Models\User;
use App\Repositories\Interfaces\UnitKerjaFolderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class UnitKerjaFolderRepository implements UnitKerjaFolderRepositoryInterface
{
    /**
     * Daftar subfolder standar untuk management mutu
     */
    private array $standardSubfolders = [
        'laporan-imut' => [
            'name' => 'Laporan IMUT',
            'description' => 'Dokumen laporan Indikator Mutu (IMUT)',
            'color' => 'blue',
        ],
        'dokumen-mutu' => [
            'name' => 'Dokumen Mutu',
            'description' => 'Dokumen sistem manajemen mutu',
            'color' => 'green',
        ],
        'sop-panduan' => [
            'name' => 'SOP & Panduan',
            'description' => 'Standard Operating Procedure dan panduan kerja',
            'color' => 'purple',
        ],
        'data-pendukung' => [
            'name' => 'Data Pendukung',
            'description' => 'Data dan file pendukung lainnya',
            'color' => 'yellow',
        ],
        'evaluasi-audit' => [
            'name' => 'Evaluasi & Audit',
            'description' => 'Hasil evaluasi dan audit mutu',
            'color' => 'red',
        ],
    ];

    public function createFolder(UnitKerja $unitKerja): void
    {
        $user = Auth::user();

        // Buat folder utama unit kerja dengan nama proper (bisa pakai spasi)
        $mainFolder = Folder::create([
            'name' => $unitKerja->unit_name, // Nama asli dengan spasi
            'description' => "Media untuk Unit Kerja: {$unitKerja->unit_name}",
            'collection' => Str::slug($unitKerja->unit_name), // Collection tetap slug untuk consistency
            'color' => null,
            'is_protected' => false,
            'is_hidden' => false,
            'is_favorite' => false,
            'is_public' => true,
            'has_user_access' => false,
            'model_type' => null,
            'model_id' => null,
            'user_id' => $user?->id ?? 1,
            'user_type' => $user ? get_class($user) : User::class,
        ]);

        // Buat subfolder standar untuk management mutu
        $this->createStandardSubfolders($mainFolder, $unitKerja, $user);

        $count = count($this->standardSubfolders);
        Log::info("📁 Folder utama dan {$count} subfolder berhasil dibuat untuk: {$unitKerja->unit_name}");
    }

    /**
     * Buat subfolder standar untuk management mutu
     */
    private function createStandardSubfolders(Folder $parentFolder, UnitKerja $unitKerja, ?User $user): void
    {
        foreach ($this->standardSubfolders as $slug => $config) {
            Folder::create([
                'name' => $config['name'], // Gunakan nama proper dari config
                'description' => $config['description'],
                'collection' => Str::slug($unitKerja->unit_name . '-' . $slug), // Collection tetap slug
                'color' => $config['color'],
                'is_protected' => false,
                'is_hidden' => false,
                'is_favorite' => false,
                'is_public' => true,
                'has_user_access' => false,
                'model_type' => null,
                'model_id' => null,
                'user_id' => $user?->id ?? 1,
                'user_type' => $user ? get_class($user) : User::class,
                'parent_id' => $parentFolder->id, // Set parent_id untuk nested structure
            ]);
        }
    }

    public function updateFolder(UnitKerja $unitKerja): void
    {
        $collection = Str::slug($unitKerja->unit_name);

        $folder = Folder::where('collection', $collection)
            ->whereNull('parent_id')
            ->first();

        if ($folder) {
            $folder->update([
                'name' => $unitKerja->unit_name, // Update dengan nama proper
                'description' => "Updated folder for Unit Kerja: {$unitKerja->unit_name}",
                'collection' => $collection,
            ]);
        } else {
            Log::warning("⚠️ Folder tidak ditemukan saat update UnitKerja ID {$unitKerja->id}");
        }
    }

    public function markFolderAsDeleted(UnitKerja $unitKerja): void
    {
        $collection = Str::slug($unitKerja->unit_name);

        $folder = Folder::where('collection', $collection)
            ->whereNull('parent_id')
            ->first();

        if ($folder && ! str_starts_with($folder->name, '[Dihapus]')) {
            // Update folder utama
            $folder->update([
                'name' => '[Dihapus] ' . $folder->name,
                'color' => 'gray',
            ]);

            // Update semua subfolder juga
            Folder::where('parent_id', $folder->id)->each(function ($subfolder) {
                if (! str_starts_with($subfolder->name, '[Dihapus]')) {
                    $subfolder->update([
                        'name' => '[Dihapus] ' . $subfolder->name,
                        'color' => 'gray',
                    ]);
                }
            });
        } elseif (! $folder) {
            Log::warning("⚠️ Folder tidak ditemukan saat mencoba menandai sebagai dihapus untuk UnitKerja ID {$unitKerja->id}");
        }
    }

    public function restoreFolder(UnitKerja $unitKerja): void
    {
        $collection = Str::slug($unitKerja->unit_name);

        $folder = Folder::where('collection', $collection)
            ->whereNull('parent_id')
            ->where('name', 'like', '[Dihapus] %')
            ->first();

        if ($folder) {
            // Restore folder utama
            $folder->update([
                'name' => $unitKerja->unit_name, // Restore dengan nama proper
                'color' => null,
            ]);

            // Restore semua subfolder juga
            Folder::where('parent_id', $folder->id)
                ->where('name', 'like', '[Dihapus] %')
                ->each(function ($subfolder) {
                    $originalName = str_replace('[Dihapus] ', '', $subfolder->name);

                    // Cari warna asli dari config berdasarkan nama
                    $originalColor = null;
                    foreach ($this->standardSubfolders as $slug => $config) {
                        if ($originalName === $config['name']) {
                            $originalColor = $config['color'];
                            break;
                        }
                    }

                    $subfolder->update([
                        'name' => $originalName,
                        'color' => $originalColor,
                    ]);
                });
        } else {
            Log::warning("⚠️ Folder yang dihapus tidak ditemukan saat restore UnitKerja ID {$unitKerja->id}");
        }
        Log::warning("⚠️ Folder tidak ditemukan saat mencoba memulihkan nama untuk UnitKerja ID {$unitKerja->id}");
    }
}
