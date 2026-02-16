<?php

namespace App\Console\Commands;

use App\Jobs\SyncLocalToS3Job;
use App\Support\StorageFallback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncLocalToS3 extends Command
{
    protected $signature = 'storage:sync-local-to-s3 {paths? : Comma-separated paths on `local` disk (default: "ttd")} {--delete : Delete local copy after successful sync} {--dry-run : Do not perform upload, only show what would be done}';

    protected $description = 'Sync files from `local` disk to S3 (used as fallback->sync)';

    public function handle(): int
    {
        $pathsOption = $this->argument('paths') ?? 'ttd';
        $paths = array_filter(array_map('trim', explode(',', $pathsOption)));

        if (!StorageFallback::isS3Available()) {
            $this->error('MinIO/S3 not available — aborting sync.');
            return 1;
        }

        $this->info('Starting sync from local -> s3 for paths: ' . implode(', ', $paths));

        if ($this->option('dry-run')) {
            // run a lightweight discovery
            $local = \Illuminate\Support\Facades\Storage::disk('local');
            foreach ($paths as $path) {
                $files = $local->allFiles($path);
                $this->line("  [DRY] Found " . count($files) . " files in local/{$path}");
            }

            $this->info('Dry-run complete.');
            return 0;
        }

        // Dispatch the queued job so it runs asynchronously if queue worker is configured
        SyncLocalToS3Job::dispatch($paths, $this->option('delete'));

        $this->info('Sync job dispatched (background). Check logs for progress.');

        return 0;
    }
}
