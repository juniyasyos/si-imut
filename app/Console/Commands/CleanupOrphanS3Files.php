<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CleanupOrphanS3Files extends Command
{
    protected $signature = 'media:cleanup-s3 {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Cleanup orphan files in S3 that are not in database';

    public function handle()
    {
        $disk = Storage::disk(config('media-library.disk_name', 's3'));
        $dryRun = $this->option('dry-run');

        $this->info('Scanning S3 for files...');

        // Get all files from S3
        $allFiles = $disk->allFiles();
        $this->info('Found ' . count($allFiles) . ' files in S3');

        // Get all media paths from database
        $mediaInDb = Media::all()->map(function ($media) {
            return $media->getPathRelativeToRoot();
        })->toArray();

        $this->info('Found ' . count($mediaInDb) . ' files in database');

        // Find orphan files (in S3 but not in DB)
        $orphanFiles = array_diff($allFiles, $mediaInDb);

        if (count($orphanFiles) === 0) {
            $this->info('✓ No orphan files found');
            return 0;
        }

        $this->warn('Found ' . count($orphanFiles) . ' orphan files:');

        foreach ($orphanFiles as $file) {
            if ($dryRun) {
                $this->line("  [DRY RUN] Would delete: {$file}");
            } else {
                $this->line("  Deleting: {$file}");
                $disk->delete($file);
            }
        }

        if ($dryRun) {
            $this->info("\n✓ Dry run completed. Use without --dry-run to actually delete files.");
        } else {
            $this->info("\n✓ Cleanup completed. Deleted " . count($orphanFiles) . " orphan files.");
        }

        return 0;
    }
}
