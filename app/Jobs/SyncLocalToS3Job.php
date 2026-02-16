<?php

namespace App\Jobs;

use App\Support\StorageFallback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncLocalToS3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $paths;

    public bool $deleteLocalAfterSync = false;

    /**
     * @param array $paths Relative paths on the `local` disk to sync. Example: ['ttd', 'uploads/tmp']
     */
    public function __construct(array $paths = ['ttd'], bool $deleteLocalAfterSync = false)
    {
        $this->paths = $paths;
        $this->deleteLocalAfterSync = $deleteLocalAfterSync;
    }

    public function handle(): void
    {
        if (!StorageFallback::isS3Available()) {
            Log::info('SyncLocalToS3Job aborted: S3 not available');
            return;
        }

        $local = Storage::disk('local');
        $public = Storage::disk('public');
        $s3 = Storage::disk('s3');

        foreach ($this->paths as $path) {
            // collect files from both public and local (avoid duplicates)
            $publicFiles = $public->exists($path) ? $public->allFiles($path) : [];
            $localFiles = $local->exists($path) ? $local->allFiles($path) : [];

            $files = array_values(array_unique(array_merge($publicFiles, $localFiles)));

            foreach ($files as $file) {
                // determine source disk
                $sourceDiskName = $public->exists($file) ? 'public' : ($local->exists($file) ? 'local' : null);
                if ($sourceDiskName === null) {
                    Log::warning("SyncLocalToS3Job: source file not found on public/local: {$file}");
                    continue;
                }

                $source = ($sourceDiskName === 'public') ? $public : $local;

                try {
                    // Skip directories
                    if ($source->mimeType($file) === null) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    // continue attempting copy
                }

                if ($s3->exists($file)) {
                    Log::debug("SyncLocalToS3Job: already exists on s3: {$file}");
                    continue;
                }

                Log::info("SyncLocalToS3Job: uploading {$file} to s3 (source={$sourceDiskName})...");

                $stream = $source->readStream($file);
                if ($stream === false) {
                    Log::warning("SyncLocalToS3Job: failed to open stream for {$sourceDiskName} file {$file}");
                    continue;
                }

                try {
                    $s3->put($file, $stream, 'public');
                    Log::info("SyncLocalToS3Job: uploaded {$file} → s3");

                    // if the source was `local`, ensure a public copy exists so /storage/... dapat diakses
                    if ($sourceDiskName === 'local' && !$public->exists($file)) {
                        // copy using stream to avoid double buffering large files
                        $localStream = $local->readStream($file);
                        if ($localStream !== false) {
                            $public->put($file, $localStream, 'public');
                            if (is_resource($localStream)) fclose($localStream);
                            Log::info("SyncLocalToS3Job: copied {$file} from local → public");
                        }
                    }

                    if ($this->deleteLocalAfterSync && $local->exists($file)) {
                        $local->delete($file);
                        Log::info("SyncLocalToS3Job: deleted local copy {$file}");
                    }
                } catch (\Throwable $e) {
                    Log::error("SyncLocalToS3Job: failed to upload {$file} — " . $e->getMessage());
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }
        }
    }
}
