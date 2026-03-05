<?php

namespace App\Console\Commands;

use App\Models\ImutPenilaian;
use Illuminate\Console\Command;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class PenilaianMediaSummaryCommand extends Command
{
    protected $signature = 'penilaian:media-summary
                            {--details : Show detailed issues}
                            {--pattern : Analyze storage pattern}
                            {--filter= : Filter by unit name}';

    protected $description = 'Show summary of all ImutPenilaian media files and storage';

    public function handle()
    {
        // Jika hanya pattern, fokus pada pattern analysis saja
        if ($this->option('pattern') && !$this->option('details')) {
            return $this->analyzeStoragePattern();
        }

        $this->line("\n" . str_repeat("=", 100));
        $this->info("IMUT PENILAIAN MEDIA SUMMARY");
        $this->line(str_repeat("=", 100) . "\n");

        $query = ImutPenilaian::query();

        // Filter by unit jika ada
        if ($this->option('filter')) {
            $unitFilter = $this->option('filter');
            $query->whereHas('laporanUnitKerja.unitKerja', function ($q) use ($unitFilter) {
                $q->where('unit_name', 'like', "%{$unitFilter}%");
            });
        }

        $allPenilaians = $query->with(['profile', 'laporanUnitKerja.unitKerja', 'laporanUnitKerja.laporanImut'])->get();

        // ===== SUMMARY STATS =====
        $this->newLine();
        $this->info("📊 OVERALL STATISTICS");

        $totalPenilaians = $allPenilaians->count();
        $this->line("   Total Penilaians: <fg=cyan>{$totalPenilaians}</>");

        $penilaianWithMedia = $allPenilaians->filter(fn($p) => $p->getMedia('*')->count() > 0)->count();
        $penilaianWithoutMedia = $totalPenilaians - $penilaianWithMedia;

        $this->line("   With Media: <fg=green>{$penilaianWithMedia}</> | Without Media: <fg=yellow>{$penilaianWithoutMedia}</>");

        // ===== MEDIA STATS =====
        $this->newLine();
        $this->info("📄 MEDIA FILES STATISTICS");

        $allMedia = Media::where('model_type', ImutPenilaian::class)->get();
        $totalMediaFiles = $allMedia->count();
        $totalMediaSize = $allMedia->sum('size');

        $this->line("   Total Media Files: <fg=cyan>{$totalMediaFiles}</>");
        $this->line("   Total Size: <fg=cyan>" . $this->formatBytes($totalMediaSize) . "</>");

        // Count per disk
        $mediaByDisk = $allMedia->groupBy('disk');
        $this->newLine();
        $this->line("   <fg=white>By Disk:</>");
        foreach ($mediaByDisk as $disk => $medias) {
            $count = $medias->count();
            $size = $medias->sum('size');
            $this->line("      • {$disk}: {$count} files (" . $this->formatBytes($size) . ")");
        }

        // Count per collection
        $mediaByCollection = $allMedia->groupBy('collection_name');
        $collectionsCount = $mediaByCollection->count();
        $this->newLine();
        $this->line("   <fg=white>Collections:</> {$collectionsCount} unique");
        if ($this->option('details')) {
            foreach ($mediaByCollection as $collection => $medias) {
                $count = $medias->count();
                $this->line("      • {$collection}: {$count} files");
            }
        }

        // ===== DISK EXISTENCE CHECK =====
        $this->newLine();
        $this->info("💾 STORAGE HEALTH CHECK");

        $filesExist = 0;
        $filesMissing = 0;
        $diskErrors = [];

        foreach ($allMedia as $media) {
            try {
                $disk = Storage::disk($media->disk);
                $path = $media->getPath();

                if ($disk->exists($path)) {
                    $filesExist++;
                } else {
                    $filesMissing++;
                    if ($this->option('details')) {
                        $this->line("      ❌ Missing: {$media->file_name} (Collection: {$media->collection_name})");
                    }
                }
            } catch (\Exception $e) {
                $diskErrors[$media->disk][] = $media->file_name;
            }
        }

        $existsPercentage = $totalMediaFiles > 0 ? round(($filesExist / $totalMediaFiles) * 100, 2) : 0;

        $this->line("   Files Exist: <fg=green>{$filesExist}</> ({$existsPercentage}%)");
        $this->line("   Files Missing: <fg=red>{$filesMissing}</> (" . (100 - $existsPercentage) . "%)");

        if (!empty($diskErrors)) {
            $this->newLine();
            $this->line("   <fg=red>Disk Access Errors:</>");
            foreach ($diskErrors as $disk => $files) {
                $this->line("      • {$disk}: " . count($files) . " files");
            }
        }

        // ===== FOLDER ASSOCIATIONS =====
        $this->newLine();
        $this->info("🗂️  FOLDER ASSOCIATIONS");

        $foldersCount = Folder::count();
        $this->line("   Total Folders: <fg=cyan>{$foldersCount}</>");

        // Folders with media
        $folderCollections = $mediaByCollection->keys();
        $foldersWithMappedMedia = Folder::whereIn('collection', $folderCollections)->count();
        $foldersNoMedia = $foldersCount - $foldersWithMappedMedia;

        $this->line("   Folders with Media: <fg=green>{$foldersWithMappedMedia}</>");
        $this->line("   Folders without Media: <fg=yellow>{$foldersNoMedia}</>");

        // ===== COLLECTION CONSISTENCY =====
        $this->newLine();
        $this->info("🔍 COLLECTION CONSISTENCY CHECK");

        $inconsistencies = [];

        foreach ($mediaByCollection as $collectionName => $medias) {
            $folder = Folder::where('collection', $collectionName)->first();

            if (!$folder) {
                $inconsistencies[] = "Collection '{$collectionName}' has {$medias->count()} files but NO folder exists";
            }
        }

        // Check for folders without media
        $foldersWithoutMedia = Folder::whereNotIn('collection', $folderCollections)->get();
        foreach ($foldersWithoutMedia as $folder) {
            $inconsistencies[] = "Folder '{$folder->name}' (collection: {$folder->collection}) has NO media files";
        }

        if (empty($inconsistencies)) {
            $this->line("   <fg=green>✅ All collections are properly mapped!</>");
        } else {
            $this->line("   <fg=red>⚠️  {$count} Inconsistencies Found:</>");
            foreach ($inconsistencies as $issue) {
                if ($this->option('details')) {
                    $this->line("      • {$issue}");
                }
            }
            $this->line("      (use --details for details)");
        }

        // ===== UNIT KERJA BREAKDOWN =====
        $this->newLine();
        $this->info("🏥 BY UNIT KERJA");

        $penilaianByUnit = $allPenilaians->groupBy(function ($p) {
            return $p->laporanUnitKerja?->unitKerja?->unit_name ?? 'Unknown';
        });

        $unitData = [];
        foreach ($penilaianByUnit as $unitName => $penilaians) {
            $mediaCount = 0;
            $mediaSize = 0;

            foreach ($penilaians as $p) {
                $medias = $p->getMedia('*');
                $mediaCount += $medias->count();
                $mediaSize += $medias->sum('size');
            }

            $unitData[] = [
                'name' => $unitName,
                'penilaians' => $penilaians->count(),
                'mediaFiles' => $mediaCount,
                'mediaSize' => $mediaSize
            ];
        }

        // Sort by media count
        usort($unitData, fn($a, $b) => $b['mediaFiles'] <=> $a['mediaFiles']);

        foreach ($unitData as $unit) {
            $percentOf = $totalMediaFiles > 0 ? round(($unit['mediaFiles'] / $totalMediaFiles) * 100, 1) : 0;
            $this->line("   • <fg=white>{$unit['name']}</> | Penilaian: {$unit['penilaians']} | Media: {$unit['mediaFiles']} ({$percentOf}%) | Size: " . $this->formatBytes($unit['mediaSize']));
        }

        // ===== ISSUES SUMMARY =====
        $this->newLine();
        $this->info("⚠️  ISSUES SUMMARY");

        $issues = [];

        if ($filesMissing > 0) {
            $issues[] = "<fg=red>❌ {$filesMissing} media files missing from storage</>";
        }

        if (!empty($inconsistencies)) {
            $issues[] = "<fg=yellow>⚠️  " . count($inconsistencies) . " collection inconsistencies found</>";
        }

        if ($penilaianWithoutMedia > 0) {
            $issues[] = "<fg=yellow>ℹ️  {$penilaianWithoutMedia} penilaians without media files</>";
        }

        // Check for duplicate collections
        $duplicateCollections = $mediaByCollection->filter(
            fn($medias, $collection) =>
            Folder::where('collection', $collection)->count() > 1
        );

        if ($duplicateCollections->count() > 0) {
            $issues[] = "<fg=red>❌ " . $duplicateCollections->count() . " collections mapped to multiple folders</>";
        }

        if (empty($issues)) {
            $this->line("   <fg=green>✅ No major issues detected!</>");
        } else {
            foreach ($issues as $issue) {
                $this->line("   {$issue}");
            }
        }

        $this->newLine();
        $this->line(str_repeat("=", 100));
        $this->info("Summary generated at " . now()->format('Y-m-d H:i:s'));
        $this->line(str_repeat("=", 100) . "\n");

        return 0;
    }

    private function analyzeStoragePattern()
    {
        $query = ImutPenilaian::query();

        // Filter by unit jika ada
        if ($this->option('filter')) {
            $unitFilter = $this->option('filter');
            $query->whereHas('laporanUnitKerja.unitKerja', function ($q) use ($unitFilter) {
                $q->where('unit_name', 'like', "%{$unitFilter}%");
            });
        }

        $allPenilaians = $query->with(['laporanUnitKerja.unitKerja'])->get();

        // Get all media
        $allMedia = Media::where('model_type', ImutPenilaian::class)->get();

        $correctPattern = 0;
        $incorrectPattern = 0;
        $patternDetails = [];

        foreach ($allMedia as $media) {
            // Get penilaian dan unit name
            $penilaian = ImutPenilaian::find($media->model_id);
            if (!$penilaian || !$penilaian->laporanUnitKerja?->unitKerja) {
                continue;
            }

            // Apply filter jika ada
            if ($this->option('filter')) {
                $unitFilter = $this->option('filter');
                if (stripos($penilaian->laporanUnitKerja->unitKerja->unit_name, $unitFilter) === false) {
                    continue;
                }
            }

            $unitName = $penilaian->laporanUnitKerja->unitKerja->unit_name;
            $unitSlug = \Illuminate\Support\Str::slug($unitName);
            $path = $media->getPath();

            // Check if path contains unit name or unit slug
            $hasUnitPattern = strpos(strtolower($path), $unitSlug) !== false 
                || strpos(strtolower($path), strtolower($unitName)) !== false;

            if ($hasUnitPattern) {
                $correctPattern++;
            } else {
                $incorrectPattern++;
                $patternDetails[] = [
                    'file' => $media->file_name,
                    'unit' => $unitName,
                    'unit_slug' => $unitSlug,
                    'path' => str_replace('/home/juni/projects/SIIMUT/storage/app/public/', '', $path),
                ];
            }
        }

        $totalAnalyzed = $correctPattern + $incorrectPattern;
        $correctPercentage = $totalAnalyzed > 0 ? round(($correctPattern / $totalAnalyzed) * 100, 2) : 0;
        $incorrectPercentage = 100 - $correctPercentage;

        // Output
        $this->line("\n" . str_repeat("=", 100));
        $this->info("STORAGE PATTERN ANALYSIS");
        $this->line(str_repeat("=", 100) . "\n");

        $this->line("Pattern Expected: <fg=white>{unit_name}/filename</>");
        $this->newLine();

        $this->line("Total Media Analyzed: <fg=cyan>{$totalAnalyzed}</>");
        $this->newLine();

        $this->line("<fg=green>✅ Correct Pattern:</> {$correctPattern} files (<fg=green>{$correctPercentage}%</>)");
        $this->line("<fg=red>❌ Incorrect Pattern:</> {$incorrectPattern} files (<fg=red>{$incorrectPercentage}%</>)");

        if (!empty($patternDetails)) {
            $this->newLine();
            $this->line("<fg=yellow>Files with Incorrect Pattern:</>");
            $this->newLine();

            // Group by unit
            $grouped = [];
            foreach ($patternDetails as $detail) {
                if (!isset($grouped[$detail['unit']])) {
                    $grouped[$detail['unit']] = [];
                }
                $grouped[$detail['unit']][] = $detail;
            }

            foreach ($grouped as $unit => $files) {
                $fileCount = count($files);
                $unitSlug = $files[0]['unit_slug'] ?? '';
                $this->line("   <fg=white>{$unit}</> (<fg=gray>{$unitSlug}</>) - {$fileCount} files");
                $this->newLine();
                foreach ($files as $file) {
                    $this->line("      • {$file['file']}");
                    $this->line("        📁 Current: {$file['path']}");
                    $this->line("        📁 Expected: {$file['unit_slug']}/...");
                }
            }
        }

        $this->newLine();
        $this->line(str_repeat("=", 100));
        $this->info("Pattern analysis completed at " . now()->format('Y-m-d H:i:s'));
        $this->line(str_repeat("=", 100) . "\n");

        return 0;
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
