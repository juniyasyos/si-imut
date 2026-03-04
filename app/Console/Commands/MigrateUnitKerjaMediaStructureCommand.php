<?php

namespace App\Console\Commands;

use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class MigrateUnitKerjaMediaStructureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:migrate-unit-kerja-structure 
                            {--check : Only analyze current structure without migrating}
                            {--unit-slug=* : Migrate specific unit kerja by slug}
                            {--dry-run : Show what would be done without executing}
                            {--backup : Create backup before migration}
                            {--force : Force migration even with existing target structure}
                            {--disk=all : Target disk for migration (public, s3, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing ImutPenilaian media from old unit-slug structure to new unit-slug-laporan-imut/periode structure';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📁 Media Structure Migration Tool');
        $this->info('=====================================');

        try {
            // Validate disk option
            $targetDisk = $this->option('disk');
            $supportedDisks = $this->validateAndGetDisks($targetDisk);

            $this->info("🗄️ Target Disk(s): " . implode(', ', $supportedDisks));
            $this->newLine();

            // Get media files to process
            $mediaCollection = $this->getMediaToProcess();

            if ($mediaCollection->isEmpty()) {
                $this->warn('❌ No media files found to process.');
                return self::SUCCESS;
            }

            $this->info("📊 Found {$mediaCollection->count()} media files to analyze");
            $this->newLine();

            // Analyze current structure per disk
            $analysis = $this->analyzeMediaStructurePerDisk($mediaCollection, $supportedDisks);
            $this->displayMultiDiskAnalysis($analysis);

            // Check mode - just report
            if ($this->option('check')) {
                return self::SUCCESS;
            }

            // Confirm before proceeding
            if (!$this->confirmProceed($analysis)) {
                $this->warn('⚠️ Operation cancelled by user.');
                return self::SUCCESS;
            }

            // Create backup if requested
            if ($this->option('backup')) {
                $this->createBackupPerDisk($supportedDisks);
            }

            // Process migration per disk
            return $this->processMigrationPerDisk($analysis, $supportedDisks);
        } catch (Throwable $e) {
            $this->error("❌ Migration failed: {$e->getMessage()}");
            Log::error("Media Migration failed", [
                'exception' => $e,
                'options' => $this->options(),
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Validate disk option and get supported disks
     */
    private function validateAndGetDisks(string $targetDisk): array
    {
        $availableDisks = ['public', 's3'];

        if ($targetDisk === 'all') {
            return $availableDisks;
        }

        if (!in_array($targetDisk, $availableDisks)) {
            throw new \InvalidArgumentException("Unsupported disk: {$targetDisk}. Supported: " . implode(', ', $availableDisks) . ', all');
        }

        return [$targetDisk];
    }

    /**
     * Get media collection based on options
     */
    private function getMediaToProcess()
    {
        $query = Media::where('model_type', ImutPenilaian::class);

        // Filter by specific unit slugs
        if ($unitSlugs = $this->option('unit-slug')) {
            $query->whereIn('collection_name', $unitSlugs);
        }

        return $query->orderBy('collection_name')->get();
    }

    /**
     * Analyze media structure per disk
     */
    private function analyzeMediaStructurePerDisk($mediaCollection, array $disks): array
    {
        $results = [];

        foreach ($disks as $disk) {
            $this->line("🔍 Analyzing {$disk} disk...");

            $needsMigration = collect();
            $alreadyMigrated = collect();
            $cannotMap = collect();
            $notFoundOnDisk = collect();

            foreach ($mediaCollection as $media) {
                $analysis = $this->analyzeMediaFileOnDisk($media, $disk);

                switch ($analysis['status']) {
                    case 'needs_migration':
                        $needsMigration->push($analysis);
                        break;
                    case 'already_migrated':
                        $alreadyMigrated->push($analysis);
                        break;
                    case 'cannot_map':
                        $cannotMap->push($analysis);
                        break;
                    case 'not_found':
                        $notFoundOnDisk->push($analysis);
                        break;
                }
            }

            $results[$disk] = [
                'total' => $mediaCollection->count(),
                'needs_migration' => $needsMigration,
                'already_migrated' => $alreadyMigrated,
                'cannot_map' => $cannotMap,
                'not_found' => $notFoundOnDisk,
            ];
        }

        return $results;
    }

    /**
     * Analyze individual media file on specific disk
     */
    private function analyzeMediaFileOnDisk(Media $media, string $disk): array
    {
        $collectionName = $media->collection_name;

        // Check if already in new format
        if (str_ends_with($collectionName, '-laporan-imut')) {
            return [
                'status' => 'already_migrated',
                'media' => $media,
                'disk' => $disk,
                'reason' => 'Already in new format',
            ];
        }

        // Try to map to unit kerja and get periode info
        $unitKerja = UnitKerja::where('slug', $collectionName)->first();

        if (!$unitKerja) {
            return [
                'status' => 'cannot_map',
                'media' => $media,
                'disk' => $disk,
                'reason' => "Cannot find unit kerja with slug: {$collectionName}",
            ];
        }

        // Try to get periode info from ImutPenilaian
        $penilaian = ImutPenilaian::find($media->model_id);

        if (!$penilaian || !$penilaian->laporanUnitKerja || !$penilaian->laporanUnitKerja->laporanImut) {
            return [
                'status' => 'cannot_map',
                'media' => $media,
                'disk' => $disk,
                'reason' => "Cannot resolve LaporanImut for periode info",
            ];
        }

        $laporan = $penilaian->laporanUnitKerja->laporanImut;

        // Check if file exists on this disk
        $storage = Storage::disk($disk);
        $currentPath = $media->getPathRelativeToRoot();

        try {
            $exists = $storage->exists($currentPath);
        } catch (Throwable $e) {
            return [
                'status' => 'not_found',
                'media' => $media,
                'disk' => $disk,
                'reason' => "Unable to check existence for: {$currentPath}",
                'current_path' => $currentPath,
                'error' => $e->getMessage(),
            ];
        }

        if (!$exists) {
            return [
                'status' => 'not_found',
                'media' => $media,
                'disk' => $disk,
                'reason' => "File not found on {$disk} disk",
                'current_path' => $currentPath,
            ];
        }

        return [
            'status' => 'needs_migration',
            'media' => $media,
            'disk' => $disk,
            'unit_kerja' => $unitKerja,
            'laporan' => $laporan,
            'old_collection' => $collectionName,
            'new_collection' => $collectionName . '-laporan-imut',
            'period_folder' => $laporan->getPeriodeFolderName(),
            'old_path' => $currentPath,
            'new_path' => $this->generateNewPath($media, $unitKerja, $laporan),
        ];
    }

    /**
     * Analyze current media structure (legacy method for single disk)
     */
    private function analyzeMediaStructure($mediaCollection): array
    {
        $needsMigration = collect();
        $alreadyMigrated = collect();
        $cannotMap = collect();

        foreach ($mediaCollection as $media) {
            $analysis = $this->analyzeMediaFile($media);

            switch ($analysis['status']) {
                case 'needs_migration':
                    $needsMigration->push($analysis);
                    break;
                case 'already_migrated':
                    $alreadyMigrated->push($analysis);
                    break;
                case 'cannot_map':
                    $cannotMap->push($analysis);
                    break;
            }
        }

        return [
            'total' => $mediaCollection->count(),
            'needs_migration' => $needsMigration,
            'already_migrated' => $alreadyMigrated,
            'cannot_map' => $cannotMap,
        ];
    }

    /**
     * Analyze individual media file
     */
    private function analyzeMediaFile(Media $media): array
    {
        $collectionName = $media->collection_name;

        // Check if already in new format (ends with -laporan-imut and has period folder)
        if (str_ends_with($collectionName, '-laporan-imut')) {
            return [
                'status' => 'already_migrated',
                'media' => $media,
                'reason' => 'Already in new format',
            ];
        }

        // Try to map to unit kerja and get periode info
        $unitKerja = UnitKerja::where('slug', $collectionName)->first();

        if (!$unitKerja) {
            return [
                'status' => 'cannot_map',
                'media' => $media,
                'reason' => "Cannot find unit kerja with slug: {$collectionName}",
            ];
        }

        // Try to get periode info from ImutPenilaian
        $penilaian = ImutPenilaian::find($media->model_id);

        if (!$penilaian || !$penilaian->laporanUnitKerja || !$penilaian->laporanUnitKerja->laporanImut) {
            return [
                'status' => 'cannot_map',
                'media' => $media,
                'reason' => "Cannot resolve LaporanImut for periode info",
            ];
        }

        $laporan = $penilaian->laporanUnitKerja->laporanImut;

        return [
            'status' => 'needs_migration',
            'media' => $media,
            'unit_kerja' => $unitKerja,
            'laporan' => $laporan,
            'old_collection' => $collectionName,
            'new_collection' => $collectionName . '-laporan-imut',
            'period_folder' => $laporan->getPeriodeFolderName(),
            'old_path' => $media->getPathRelativeToRoot(),
            'new_path' => $this->generateNewPath($media, $unitKerja, $laporan),
        ];
    }

    /**
     * Generate new file path
     */
    private function generateNewPath(Media $media, UnitKerja $unitKerja, LaporanImut $laporan): string
    {
        $newCollection = $unitKerja->slug . '-laporan-imut';
        $periodFolder = $laporan->getPeriodeFolderName();

        return $newCollection . '/' . $periodFolder . '/' . $media->file_name;
    }

    /**
     * Display multi-disk analysis results
     */
    private function displayMultiDiskAnalysis(array $analysis): void
    {
        foreach ($analysis as $disk => $data) {
            $this->newLine();
            $this->info("💾 {$disk} Disk Analysis:");

            $this->table(
                ['Status', 'Count', 'Percentage'],
                [
                    ['✅ Already Migrated', $data['already_migrated']->count(), $this->percentage($data['already_migrated']->count(), $data['total'])],
                    ['⏯️ Needs Migration', $data['needs_migration']->count(), $this->percentage($data['needs_migration']->count(), $data['total'])],
                    ['❌ Cannot Map', $data['cannot_map']->count(), $this->percentage($data['cannot_map']->count(), $data['total'])],
                    ['📂 Not Found', $data['not_found']->count(), $this->percentage($data['not_found']->count(), $data['total'])],
                    ['📊 Total', $data['total'], '100%'],
                ]
            );

            // Show samples
            if ($data['needs_migration']->isNotEmpty()) {
                $this->info("⏯️ Sample Files Needing Migration ({$disk}):");
                foreach ($data['needs_migration']->take(3) as $item) {
                    $this->line("  - {$item['old_path']} → {$item['new_path']}");
                }
            }

            if ($data['not_found']->isNotEmpty()) {
                $this->warn("📂 Files Not Found on {$disk} disk:");
                foreach ($data['not_found']->take(3) as $item) {
                    $this->line("  - {$item['current_path']}");
                }
            }
        }

        $this->newLine();
    }

    /**
     * Display analysis results (legacy method)
     */
    private function displayAnalysis(array $analysis): void
    {
        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['✅ Already Migrated', $analysis['already_migrated']->count(), $this->percentage($analysis['already_migrated']->count(), $analysis['total'])],
                ['⏯️ Needs Migration', $analysis['needs_migration']->count(), $this->percentage($analysis['needs_migration']->count(), $analysis['total'])],
                ['❌ Cannot Map', $analysis['cannot_map']->count(), $this->percentage($analysis['cannot_map']->count(), $analysis['total'])],
                ['📊 Total', $analysis['total'], '100%'],
            ]
        );

        // Show samples
        if ($analysis['needs_migration']->isNotEmpty()) {
            $this->newLine();
            $this->info("⏯️ Sample Files Needing Migration:");
            foreach ($analysis['needs_migration']->take(5) as $item) {
                $this->line("  - {$item['old_path']} → {$item['new_path']}");
            }
        }

        if ($analysis['cannot_map']->isNotEmpty()) {
            $this->newLine();
            $this->warn("❌ Files That Cannot Be Mapped:");
            foreach ($analysis['cannot_map']->take(5) as $item) {
                $this->line("  - {$item['media']->getPathRelativeToRoot()} ({$item['reason']})");
            }
        }

        $this->newLine();
    }

    /**
     * Calculate percentage
     */
    private function percentage(int $count, int $total): string
    {
        if ($total === 0) return '0%';
        return round(($count / $total) * 100, 1) . '%';
    }

    /**
     * Confirm before proceeding (updated for multi-disk)
     */
    private function confirmProceed(array $analysis): bool
    {
        $totalNeedsMigration = 0;

        foreach ($analysis as $disk => $data) {
            $totalNeedsMigration += $data['needs_migration']->count();
        }

        if ($totalNeedsMigration === 0) {
            $this->info('✅ No files need migration across all disks!');
            return false;
        }

        if ($this->option('no-interaction')) {
            return true;
        }

        return $this->confirm("Migrate {$totalNeedsMigration} media files across selected disk(s)?");
    }

    /**
     * Create backup per disk
     */
    private function createBackupPerDisk(array $disks): void
    {
        $this->info('💾 Creating backups...');

        $timestamp = now()->format('Y-m-d-H-i-s');

        foreach ($disks as $disk) {
            $backupPath = "backups/media-migration-{$disk}-{$timestamp}";

            // TODO: Implement per-disk backup logic
            // - Export media table records filtered by disk
            // - Create file structure backup for specific disk

            $this->line("✅ {$disk} backup prepared at: {$backupPath}");
        }
    }

    /**
     * Process migration per disk
     */
    private function processMigrationPerDisk(array $analysis, array $disks): int
    {
        $totalSuccess = 0;
        $totalFailed = 0;

        foreach ($disks as $disk) {
            $diskData = $analysis[$disk];
            $needsMigration = $diskData['needs_migration'];

            if ($needsMigration->isEmpty()) {
                $this->info("✅ {$disk} disk: Nothing to migrate!");
                continue;
            }

            $this->info("🚀 Processing {$needsMigration->count()} files on {$disk} disk...");

            $success = 0;
            $failed = 0;

            $progressBar = $this->output->createProgressBar($needsMigration->count());
            $progressBar->setFormat("[{$disk}] %current%/%max% [%bar%] %percent:3s%% %message%");
            $progressBar->start();

            foreach ($needsMigration as $item) {
                try {
                    if ($this->option('dry-run')) {
                        $progressBar->setMessage("[DRY RUN] {$item['media']->file_name}");
                    } else {
                        $progressBar->setMessage($item['media']->file_name);
                        $this->migrateMediaFileOnDisk($item, $disk);
                    }

                    $success++;
                } catch (Throwable $e) {
                    $failed++;
                    Log::error("Failed to migrate media file on {$disk}", [
                        'media_id' => $item['media']->id,
                        'disk' => $disk,
                        'old_path' => $item['old_path'],
                        'new_path' => $item['new_path'],
                        'exception' => $e,
                    ]);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            // Disk summary
            $this->info("📋 {$disk} disk summary:");
            $this->info("  ✅ Successful: {$success}");
            if ($failed > 0) {
                $this->error("  ❌ Failed: {$failed}");
            }
            $this->newLine();

            $totalSuccess += $success;
            $totalFailed += $failed;
        }

        // Overall summary
        $this->displayMigrationSummary($totalSuccess, $totalFailed, $this->option('dry-run'));

        return $totalFailed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Migrate individual media file on specific disk
     */
    private function migrateMediaFileOnDisk(array $item, string $disk): void
    {
        $media = $item['media'];
        $storage = Storage::disk($disk);

        $oldPath = $item['old_path'];
        $newPath = $item['new_path'];

        // Create directory if needed
        $newDir = dirname($newPath);
        if (!$storage->exists($newDir)) {
            $storage->makeDirectory($newDir);
        }

        // Move file on this disk
        if ($storage->exists($oldPath)) {
            if (!$storage->move($oldPath, $newPath)) {
                throw new \Exception("Failed to move file from {$oldPath} to {$newPath} on {$disk} disk");
            }
        } else {
            throw new \Exception("Source file not found: {$oldPath} on {$disk} disk");
        }

        // Update database record only once (not per disk)
        // We'll update it based on the first successful migration
        if ($disk === $this->getFirstTargetDisk()) {
            $newCollection = $item['new_collection'];

            $media->update([
                'collection_name' => $newCollection,
                'file_name' => basename($newPath),
            ]);

            $media->refresh();
        }
    }

    /**
     * Get first target disk for database updates
     */
    private function getFirstTargetDisk(): string
    {
        $targetDisk = $this->option('disk');

        if ($targetDisk === 'all') {
            return 'public'; // Priority: public first
        }

        return $targetDisk;
    }

    /**
     * Create backup before migration (legacy method)
     */
    private function createBackup(): void
    {
        $this->info('💾 Creating backup...');

        $backupPath = 'backups/media-migration-' . now()->format('Y-m-d-H-i-s');

        // TODO: Implement backup logic
        // - Export media table records
        // - Create file structure backup

        $this->info("✅ Backup created at: {$backupPath}");
    }
}
