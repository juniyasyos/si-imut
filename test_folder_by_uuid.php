#!/usr/bin/env php
<?php

use App\Models\ImutPenilaian;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$folderUuid = $argc > 1 ? $argv[1] : null;

if (!$folderUuid) {
    echo "❌ UUID folder harus disediakan!\n";
    echo "Usage: php test_folder_by_uuid.php <folder_uuid>\n\n";
    echo "Contoh: php test_folder_by_uuid.php dbaa6228-62df-4f51-8628-20fc4ec218b8\n";
    exit(1);
}

echo "\n========================================\n";
echo "FOLDER RELASI TEST by UUID\n";
echo "========================================\n\n";

$folder = Folder::where('uuid', $folderUuid)->first();

if (!$folder) {
    echo "❌ Folder dengan UUID {$folderUuid} tidak ditemukan!\n";
    exit(1);
}

echo "📁 FOLDER: {$folder->name}\n";
echo "   ID: {$folder->id}\n";
echo "   UUID: {$folder->uuid}\n";
echo "   Collection: {$folder->collection}\n";
echo "   Parent ID: {$folder->parent_id}\n";

if ($folder->parent_id) {
    $parent = Folder::find($folder->parent_id);
    if ($parent) {
        echo "   Parent: {$parent->name}\n";
    }
}

echo "\n";

// Media files
$mediaCount = Media::where('collection_name', $folder->collection)->count();
echo "📄 MEDIA FILES: {$mediaCount}\n";

$medias = Media::where('collection_name', $folder->collection)->limit(10)->get();
foreach ($medias as $media) {
    echo "   - {$media->file_name} ({$media->size} bytes)\n";
    echo "     Model: {$media->model_type} ID:{$media->model_id}\n";
}

echo "\n";

// Pivot entries
$pivotCount = \DB::table('folder_has_models')->where('folder_id', $folder->id)->count();
echo "🔗 PIVOT ENTRIES: {$pivotCount}\n";

$pivots = \DB::table('folder_has_models')->where('folder_id', $folder->id)->limit(10)->get();
foreach ($pivots as $pivot) {
    echo "   - {$pivot->model_type} (ID: {$pivot->model_id})\n";
}

echo "\n";

// Search for related ImutPenilaian
echo "🔍 FINDING RELATED IMUT PENILAIAN...\n";

$penilaians = ImutPenilaian::all();
$found = 0;

foreach ($penilaians as $penilaian) {
    $mediaByPenilaian = Media::where('model_type', ImutPenilaian::class)
        ->where('model_id', $penilaian->id)
        ->where('collection_name', $folder->collection)
        ->first();

    if ($mediaByPenilaian) {
        $found++;
        echo "   ✓ Penilaian ID {$penilaian->id}\n";

        if ($penilaian->laporanUnitKerja) {
            echo "     └─ LaporanUnitKerja ID: {$penilaian->laporanUnitKerja->id}\n";

            if ($penilaian->laporanUnitKerja->laporanImut) {
                $laporan = $penilaian->laporanUnitKerja->laporanImut;
                echo "        - Laporan: {$laporan->report_year}-{$laporan->report_month}\n";
            }

            if ($penilaian->laporanUnitKerja->unitKerja) {
                echo "        - Unit: {$penilaian->laporanUnitKerja->unitKerja->unit_name}\n";
            }
        }
    }
}

echo "   Total Found: {$found}\n";

echo "\n========================================\n";
echo "✅ Test selesai!\n";
echo "========================================\n\n";
