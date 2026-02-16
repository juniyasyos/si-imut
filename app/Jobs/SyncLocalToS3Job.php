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
        $s3 = Storage::disk('s3');

        foreach ($this->paths as $path) {
            $files = $local->allFiles($path);

            foreach ($files as $file) {
                try {
                    // Skip directories
                    if ($local->mimeType($file) === null) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    // if mimeType fails for some files, still attempt copy
                }

                if ($s3->exists($file)) {
                    Log::debug("SyncLocalToS3Job: already exists on s3: {$file}");
                    continue;
                }

                Log::info("SyncLocalToS3Job: uploading {$file} to s3...");

                $stream = $local->readStream($file);
                if ($stream === false) {
                    Log::warning("SyncLocalToS3Job: failed to open stream for local file {$file}");
                    continue;
                }

                try {
                    $s3->put($file, $stream, 'public');
                    Log::info("SyncLocalToS3Job: uploaded {$file} → s3");

                    if ($this->deleteLocalAfterSync) {
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
