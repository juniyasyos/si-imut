<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Http\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestTtdUpload extends Command
{
    protected $signature = 'test:ttd-upload';
    protected $description = 'Test TTD upload ke MinIO';

    public function handle()
    {
        $this->info('=== Testing MinIO TTD Upload ===');

        // 1. Cek Konfigurasi S3
        $this->line("\n1. Konfigurasi MinIO:");
        $this->line("   Endpoint: " . config('filesystems.disks.s3.endpoint'));
        $this->line("   Bucket: " . config('filesystems.disks.s3.bucket'));
        $this->line("   Access Key: " . config('filesystems.disks.s3.key'));
        $this->line("   Region: " . config('filesystems.disks.s3.region'));
        $this->line("   Use Path Style: " . (config('filesystems.disks.s3.use_path_style_endpoint') ? 'true' : 'false'));

        // 2. Test Koneksi
        $this->line("\n2. Testing Koneksi ke MinIO:");
        try {
            $disk = Storage::disk('s3');

            // Coba list files untuk test koneksi
            $files = $disk->listContents('ttd');
            $this->line("   ✓ Koneksi berhasil");
            $this->line("   Files di directory 'ttd':");

            $fileArray = iterator_to_array($files);
            if (empty($fileArray)) {
                $this->line("      (Kosong)");
            } else {
                foreach ($fileArray as $file) {
                    $this->line("      - " . $file['path']);
                }
            }
        } catch (Exception $e) {
            $this->error("   ✗ Koneksi gagal: " . $e->getMessage());
            return 1;
        }

        // 3. Test Upload
        $this->line("\n3. Testing Upload TTD:");
        try {
            // Buat test image (transparent PNG)
            $testImagePath = '/tmp/test-ttd-' . time() . '.png';
            $imageData = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
            );
            file_put_contents($testImagePath, $imageData);

            // Upload dengan cara yang sama seperti di TtdUploadComponent
            $file = fopen($testImagePath, 'r');
            $path = 'ttd/test-ttd-' . time() . '.png';

            $result = Storage::disk('s3')->put($path, $file);
            @fclose($file);

            if ($result) {
                $this->line("   ✓ Upload berhasil");
                $this->line("   Path: " . $path);

                // Cek URL yang di-generate
                $url = Storage::disk('s3')->url($path);
                $this->line("   URL: " . $url);

                // Cek apakah file bisa diakses
                if (Storage::disk('s3')->exists($path)) {
                    $this->line("   ✓ File terverifikasi di MinIO");
                } else {
                    $this->line("   ✗ File tidak ditemukan di MinIO setelah upload");
                }
            } else {
                $this->line("   ✗ Upload gagal");
            }

            @unlink($testImagePath);
        } catch (Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
            $this->error("   Stack: " . $e->getTraceAsString());
        }

        // 4. Test Upload dengan UploadedFile
        $this->line("\n4. Testing Upload dengan UploadedFile:");
        try {
            $testImagePath = '/tmp/test-ttd-putfile-' . time() . '.png';
            $imageData = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
            );
            file_put_contents($testImagePath, $imageData);

            // Gunakan Storage::putFile seperti Filament FileUpload
            $path = Storage::disk('s3')->putFile('ttd', new File($testImagePath), 'public');

            if ($path) {
                $this->line("   ✓ putFile berhasil");
                $this->line("   Path: " . $path);
                $this->line("   URL: " . Storage::disk('s3')->url($path));
            } else {
                $this->line("   ✗ putFile gagal");
            }

            @unlink($testImagePath);
        } catch (Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
        }

        // 5. List semua file di bucket
        $this->line("\n5. Semua File di Bucket 'siimut':");
        try {
            $disk = Storage::disk('s3');
            $allFiles = $disk->listContents('/', true);

            $fileArray = iterator_to_array($allFiles);
            if (empty($fileArray)) {
                $this->line("   (Bucket kosong)");
            } else {
                foreach ($fileArray as $file) {
                    if ($file['type'] !== 'file') continue;
                    $size = $file['file_size'] ?? $file['size'] ?? 0;
                    $this->line("   - " . $file['path'] . " (" . $size . " bytes)");
                }
            }
        } catch (Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
        }

        $this->info("\n=== Test Selesai ===");
        return 0;
    }
}
