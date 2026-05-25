<?php

namespace App\Console\Commands;

use Illuminate\Http\File;
use Exception;
use App\Models\User;
use Illuminate\Console\Command;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Storage;

class TestFilamentTtdUpload extends Command
{
    protected $signature = 'test:filament-ttd';
    protected $description = 'Test Filament FileUpload untuk TTD';

    public function handle()
    {
        $this->info('=== Testing Filament TTD FileUpload ===');

        // 1. Simulasi Filament FileUpload behavior
        $this->line("\n1. Testing Filament FileUpload Component:");
        try {
            // Create fake uploaded file
            $testImagePath = '/tmp/test-filament-ttd.png';
            $imageData = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
            );
            file_put_contents($testImagePath, $imageData);

            // Simulasi file upload ke temp
            $uploadedFile = new File($testImagePath);

            // Ini adalah apa yang Filament FileUpload lakukan
            $file = fopen($testImagePath, 'r');
            $path = 'ttd/' . $uploadedFile->hashName();

            $disk = Storage::disk('s3');
            $result = $disk->put($path, $file);
            @fclose($file);

            if ($result) {
                $this->line("   ✓ Filament-style upload berhasil");
                $this->line("   Path: " . $path);
                $this->line("   URL: " . $disk->url($path));

                // Return value yang akan disimpan ke database
                $this->line("   Value untuk disimpan: " . $path);
            }

            @unlink($testImagePath);
        } catch (Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
        }

        // 2. Test Filament validation rules
        $this->line("\n2. Testing Filament Validation Rules:");

        $rules = [
            'image' => true,
            'acceptedFileTypes' => ['image/png', 'image/jpeg', 'image/jpg'],
            'maxSize' => 2048, // KB
        ];

        $this->line("   Image check: " . ($rules['image'] ? 'required' : 'not required'));
        $this->line("   Accepted types: " . implode(', ', $rules['acceptedFileTypes']));
        $this->line("   Max size: " . $rules['maxSize'] . " KB");

        // 3. Check TtdUploadComponent code
        $this->line("\n3. Mengecek TtdUploadComponent:");
        $componentPath = app_path('Livewire/TtdUploadComponent.php');
        if (file_exists($componentPath)) {
            $content = file_get_contents($componentPath);

            // Check FileUpload configuration
            if (strpos($content, "->disk('s3')") !== false) {
                $this->line("   ✓ Disk S3 sudah dikonfigurasi");
            } else {
                $this->warn("   ✗ Disk S3 TIDAK dikonfigurasi!");
            }

            if (strpos($content, "->directory('ttd')") !== false) {
                $this->line("   ✓ Directory 'ttd' sudah dipilih");
            } else {
                $this->warn("   ✗ Directory 'ttd' TIDAK dipilih!");
            }

            if (strpos($content, "->image()") !== false) {
                $this->line("   ✓ Image validation aktif");
            }

            if (strpos($content, "->required(false)") !== false) {
                $this->line("   ✓ Field tidak wajib");
            }
        }

        // 4. Test dengan user model
        $this->line("\n4. Testing update dengan user:");
        try {
            $user = User::first();
            if ($user) {
                $testPath = 'ttd/test-user-' . time() . '.png';

                // Create test file
                $testImagePath = '/tmp/test-user-ttd.png';
                $imageData = base64_decode(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
                );
                file_put_contents($testImagePath, $imageData);

                // Simulate user update
                $file = fopen($testImagePath, 'r');
                $disk = Storage::disk('s3');
                if ($disk->put($testPath, $file)) {
                    @fclose($file);

                    $user->update(['ttd_url' => $testPath]);
                    $this->line("   ✓ User update berhasil");
                    $this->line("   TTD URL: " . $user->fresh()->ttd_url);
                    $this->line("   TTD Full URL: " . $disk->url($user->fresh()->ttd_url));
                }

                @unlink($testImagePath);
            } else {
                $this->warn("   ⚠ Tidak ada user di database untuk testing");
            }
        } catch (Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
        }

        // 5. Cek permissions disk S3
        $this->line("\n5. Checking S3 Disk Permissions:");
        try {
            $disk = Storage::disk('s3');

            // Coba test basic operations
            $testPath = 'ttd/permission-test-' . time() . '.txt';

            // Write
            if ($disk->put($testPath, 'test')) {
                $this->line("   ✓ Write permission: OK");
            }

            // Read
            if ($disk->exists($testPath)) {
                $this->line("   ✓ Read permission: OK");
            }

            // Delete
            if ($disk->delete($testPath)) {
                $this->line("   ✓ Delete permission: OK");
            }
        } catch (Exception $e) {
            $this->error("   ✗ Permission error: " . $e->getMessage());
        }

        $this->info("\n=== Test Selesai ===\n");
        return 0;
    }
}
