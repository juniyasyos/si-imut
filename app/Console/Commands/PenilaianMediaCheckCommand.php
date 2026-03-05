<?php

namespace App\Console\Commands;

use App\Models\ImutPenilaian;
use Illuminate\Console\Command;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PenilaianMediaCheckCommand extends Command
{
    protected $signature = 'penilaian:media-check {penilaian_id? : ID ImutPenilaian to check}';

    protected $description = 'Check ImutPenilaian media files, storage location, and folder associations';

    public function handle()
    {
        $penilaianId = $this->argument('penilaian_id');

        // Jika tidak ada parameter, cari penilaian dengan media
        if (!$penilaianId) {
            $penilaianWithMedia = ImutPenilaian::whereHas('media')->first();
            if (!$penilaianWithMedia) {
                $this->error('❌ Tidak ada ImutPenilaian dengan media!');
                return 1;
            }
            $penilaianId = $penilaianWithMedia->id;
            $this->info("Auto-detected penilaian with media: ID {$penilaianId}");
        }

        $penilaian = ImutPenilaian::find($penilaianId);
        if (!$penilaian) {
            $this->error("❌ ImutPenilaian dengan ID {$penilaianId} tidak ditemukan!");
            return 1;
        }

        $this->line(str_repeat("=", 80));
        $this->info("IMUT PENILAIAN MEDIA TEST: ID {$penilaianId}");
        $this->line(str_repeat("=", 80));
        $this->newLine();

        // 1. Penilaian Info
        $this->section("📋 IMUT PENILAIAN INFO");
        $this->line("   ID: {$penilaian->id}");
        $this->line("   Analysis: " . substr($penilaian->analysis ?? '', 0, 50) . "...");
        $this->line("   Numerator: {$penilaian->numerator_value}");
        $this->line("   Denominator: {$penilaian->denominator_value}");

        if ($penilaian->profile) {
            $this->line("   Profile: {$penilaian->profile->profile_name}");
        }

        if ($penilaian->laporanUnitKerja) {
            $this->line("   LaporanUnitKerja ID: {$penilaian->laporanUnitKerja->id}");

            if ($penilaian->laporanUnitKerja->unitKerja) {
                $this->line("   Unit Kerja: {$penilaian->laporanUnitKerja->unitKerja->unit_name}");
            }

            if ($penilaian->laporanUnitKerja->laporanImut) {
                $laporan = $penilaian->laporanUnitKerja->laporanImut;
                $this->line("   Laporan IMUT: {$laporan->report_year}-{$laporan->report_month}");
            }
        }

        $this->newLine();

        // 2. Get Media
        $this->section("📄 MEDIA FILES via getMedia()");
        $allMedia = $penilaian->getMedia('*');
        $this->line("   Total: " . $allMedia->count());
        $this->newLine();

        if ($allMedia->count() === 0) {
            $this->info("   ℹ️  Tidak ada media file");
        } else {
            foreach ($allMedia as $idx => $media) {
                $this->line("   [{$idx}] {$media->file_name}");
                $this->line("       UUID: {$media->uuid}");
                $this->line("       Size: {$media->size} bytes");
                $this->line("       Collection: {$media->collection_name}");
                $this->line("       Disk: {$media->disk}");
                $this->line("       Mime Type: {$media->mime_type}");
                $this->line("       Relative Path: {$media->getPath()}");
                $this->line("       Full URL: {$media->getFullUrl()}");

                // Check disk configuration
                $diskDriver = config('media-library.disk_name', 's3');
                $this->line("       Disk Config: {$diskDriver}");

                // Check if file exists on disk
                $disk = \Illuminate\Support\Facades\Storage::disk($media->disk);
                $exists = $disk->exists($media->getPath());
                $status = $exists ? "✅ Yes" : "❌ No";
                $this->line("       Exists on Disk: {$status}");

                $this->newLine();
            }
        }

        // 3. Check Folder Association
        $this->section("🗂️  FOLDER ASSOCIATIONS");

        // Find folder by collection_name
        $collectionName = $penilaian->getMedia('*')->first()?->collection_name;

        if ($collectionName) {
            $folders = Folder::where('collection', $collectionName)->get();
            $this->line("   Collection Name: {$collectionName}");
            $this->line("   Found Folders: " . $folders->count());
            $this->newLine();

            foreach ($folders as $folder) {
                $this->line("   📁 {$folder->name}");
                $this->line("      ID: {$folder->id}");
                $this->line("      UUID: {$folder->uuid}");
                $this->line("      Collection: {$folder->collection}");
                $this->line("      Parent ID: {$folder->parent_id}");

                if ($folder->parent_id) {
                    $parentFolder = Folder::find($folder->parent_id);
                    if ($parentFolder) {
                        $this->line("      Parent Folder: {$parentFolder->name}");
                    }
                }

                // Check pivot table
                $pivotCount = \DB::table('folder_has_models')
                    ->where('folder_id', $folder->id)
                    ->count();
                $this->line("      Pivot Entries: {$pivotCount}");

                $this->newLine();
            }
        } else {
            $this->info("   ℹ️  Tidak ada media, skip folder search");
        }

        // 4. Storage Configuration
        $this->section("💾 STORAGE CONFIGURATION");
        $mediaLibraryConfig = config('media-library');
        $this->line("   Disk: " . ($mediaLibraryConfig['disk_name'] ?? 's3'));
        $this->line("   Path Prefix: " . ($mediaLibraryConfig['prefix'] ?? '/'));

        if (($mediaLibraryConfig['disk_name'] ?? 's3') === 's3') {
            $s3Config = config('filesystems.disks.s3');
            $this->line("   S3 Bucket: " . ($s3Config['bucket'] ?? 'N/A'));
            $this->line("   S3 Region: " . ($s3Config['region'] ?? 'N/A'));
            $this->line("   S3 Key: " . (substr($s3Config['key'] ?? '', 0, 10) . "***"));
        }

        $this->newLine();

        // 5. Database Media Records
        $this->section("🗄️  DATABASE MEDIA RECORDS");
        $dbMedia = Media::where('model_type', ImutPenilaian::class)
            ->where('model_id', $penilaianId)
            ->get();

        $this->line("   Total Records: " . $dbMedia->count());
        $this->newLine();

        foreach ($dbMedia as $idx => $media) {
            $this->line("   [{$idx}] {$media->file_name}");
            $this->line("       ID: {$media->id}");
            $this->line("       UUID: {$media->uuid}");
            $this->line("       Model Type: {$media->model_type}");
            $this->line("       Model ID: {$media->model_id}");
            $this->line("       Collection Name: {$media->collection_name}");
            $this->newLine();
        }

        // 6. Pivot Table Check
        $this->section("🔗 PIVOT TABLE (folder_has_models)");
        $pivotMedia = \DB::table('folder_has_models')
            ->where('model_type', 'Spatie\MediaLibrary\MediaCollections\Models\Media')
            ->get();

        $this->line("   Total Pivots: " . count($pivotMedia));
        $this->line("   (Note: This is all media in system, not just this penilaian)");
        $this->newLine();

        // 7. Directory Structure Test
        $this->section("📂 DIRECTORY STRUCTURE");

        if ($penilaian->laporanUnitKerja && $penilaian->laporanUnitKerja->unitKerja) {
            $unitKerja = $penilaian->laporanUnitKerja->unitKerja;
            $laporan = $penilaian->laporanUnitKerja->laporanImut;

            if ($laporan) {
                $unitSlug = \Illuminate\Support\Str::slug($unitKerja->unit_name);
                $laporanDate = \Illuminate\Support\Carbon::createFromDate(
                    $laporan->report_year,
                    $laporan->report_month,
                    1
                )->locale('id')->translatedFormat('F Y');
                $laporanSlug = strtolower(str_replace(' ', '-', $laporanDate));

                $expectedPath = "siimut/{$unitSlug}/{$unitSlug}-laporan-imut/{$unitSlug}-laporan-imut-{$laporanSlug}";
                $this->line("   Unit: {$unitKerja->unit_name}");
                $this->line("   Unit Slug: {$unitSlug}");
                $this->line("   Laporan Period: {$laporan->report_year}-{$laporan->report_month}");
                $this->line("   Laporan Slug: {$laporanSlug}");
                $this->line("   Expected S3 Path: {$expectedPath}/");
                $this->newLine();
            }
        }

        // 8. Summary
        $this->section("📊 SUMMARY");
        $this->line("   Total Media Attached: " . $allMedia->count());
        $this->line("   Total Media in DB: " . $dbMedia->count());
        $this->line("   Has Folder Association: " . (isset($collectionName) && $collectionName ? "Yes" : "No"));

        $this->newLine();
        $this->line(str_repeat("=", 80));
        $this->info("✅ Test completed!");
        $this->line(str_repeat("=", 80));

        return 0;
    }

    protected function section($title)
    {
        $this->newLine();
        $this->line($title);
    }
}
