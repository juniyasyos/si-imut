<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MinioPathVerificationTest extends TestCase
{
    /**
     * Test untuk memverifikasi path dan URL MinIO
     */
    public function test_verify_minio_path_and_url_structure(): void
    {
        $disk = Storage::disk('s3');

        // Test 1: Upload ke root bucket
        echo "\n=== TEST 1: Upload ke root bucket ===\n";
        $file1 = UploadedFile::fake()->image('root-test.jpg');
        $path1 = $file1->hashName();
        $disk->put($path1, $file1->get());

        echo "Path: " . $path1 . "\n";
        echo "URL: " . $disk->url($path1) . "\n";
        echo "Exists: " . ($disk->exists($path1) ? 'YES' : 'NO') . "\n";

        $this->assertTrue($disk->exists($path1));

        // Test 2: Upload ke folder documents
        echo "\n=== TEST 2: Upload ke folder documents ===\n";
        $file2 = UploadedFile::fake()->create('document.pdf', 100);
        $path2 = 'documents/' . $file2->hashName();
        $disk->put($path2, $file2->get());

        echo "Path: " . $path2 . "\n";
        echo "URL: " . $disk->url($path2) . "\n";
        echo "Exists: " . ($disk->exists($path2) ? 'YES' : 'NO') . "\n";

        $this->assertTrue($disk->exists($path2));
        $this->assertStringContainsString('documents/', $disk->url($path2));

        // Test 3: Upload ke nested folder
        echo "\n=== TEST 3: Upload ke nested folder (images/avatars) ===\n";
        $file3 = UploadedFile::fake()->image('avatar.png');
        $path3 = 'images/avatars/' . $file3->hashName();
        $disk->put($path3, $file3->get());

        echo "Path: " . $path3 . "\n";
        echo "URL: " . $disk->url($path3) . "\n";
        echo "Exists: " . ($disk->exists($path3) ? 'YES' : 'NO') . "\n";

        $this->assertTrue($disk->exists($path3));
        $this->assertStringContainsString('images/avatars/', $disk->url($path3));

        // Test 4: Upload dengan putFileAs
        echo "\n=== TEST 4: Upload dengan putFileAs ===\n";
        $file4 = UploadedFile::fake()->create('report.pdf', 150);
        $path4 = $disk->putFileAs('reports/2024', $file4, 'annual-report.pdf');

        echo "Path: " . $path4 . "\n";
        echo "URL: " . $disk->url($path4) . "\n";
        echo "Exists: " . ($disk->exists($path4) ? 'YES' : 'NO') . "\n";

        $this->assertEquals('reports/2024/annual-report.pdf', $path4);
        $this->assertTrue($disk->exists($path4));

        // Test 5: List semua files
        echo "\n=== TEST 5: List semua files ===\n";
        $allFiles = $disk->allFiles();
        echo "Total files: " . count($allFiles) . "\n";
        foreach ($allFiles as $file) {
            echo "- " . $file . "\n";
        }

        // Test 6: Verifikasi struktur path
        echo "\n=== TEST 6: Verifikasi struktur bucket ===\n";
        $directories = $disk->allDirectories();
        echo "Directories found: " . count($directories) . "\n";
        foreach ($directories as $dir) {
            echo "- " . $dir . "\n";
        }

        // Cleanup
        echo "\n=== CLEANUP ===\n";
        $disk->delete([$path1, $path2, $path3, $path4]);
        echo "Cleanup complete\n";

        $this->assertTrue(true);
    }

    /**
     * Test untuk memverifikasi konfigurasi MinIO
     */
    public function test_verify_minio_configuration(): void
    {
        echo "\n=== MinIO Configuration ===\n";
        echo "Driver: " . config('filesystems.disks.s3.driver') . "\n";
        echo "Bucket: " . config('filesystems.disks.s3.bucket') . "\n";
        echo "Endpoint: " . config('filesystems.disks.s3.endpoint') . "\n";
        echo "Region: " . config('filesystems.disks.s3.region') . "\n";
        echo "Use Path Style: " . (config('filesystems.disks.s3.use_path_style_endpoint') ? 'true' : 'false') . "\n";
        echo "URL: " . config('filesystems.disks.s3.url') . "\n";

        $this->assertEquals('s3', config('filesystems.disks.s3.driver'));
        $this->assertNotEmpty(config('filesystems.disks.s3.bucket'));
        $this->assertTrue(config('filesystems.disks.s3.use_path_style_endpoint'));
    }

    /**
     * Test real-world scenario: Upload berbagai tipe file
     */
    public function test_real_world_file_upload_scenarios(): void
    {
        $disk = Storage::disk('s3');
        $uploadedPaths = [];

        echo "\n=== Real World Upload Scenarios ===\n";

        // Scenario 1: User avatar
        echo "\n1. User Avatar Upload\n";
        $avatar = UploadedFile::fake()->image('user-avatar.jpg', 200, 200);
        $avatarPath = 'avatars/users/' . auth()->id() ?? 1 . '/' . $avatar->hashName();
        $disk->put($avatarPath, $avatar->get());
        echo "   Path: {$avatarPath}\n";
        echo "   URL: " . $disk->url($avatarPath) . "\n";
        $uploadedPaths[] = $avatarPath;

        // Scenario 2: Document upload
        echo "\n2. Document Upload\n";
        $doc = UploadedFile::fake()->create('contract.pdf', 500);
        $docPath = 'documents/contracts/' . date('Y/m') . '/' . $doc->hashName();
        $disk->put($docPath, $doc->get());
        echo "   Path: {$docPath}\n";
        echo "   URL: " . $disk->url($docPath) . "\n";
        $uploadedPaths[] = $docPath;

        // Scenario 3: Report file with specific name
        echo "\n3. Report with Custom Name\n";
        $report = UploadedFile::fake()->create('monthly-report.xlsx', 300);
        $reportPath = $disk->putFileAs(
            'reports/' . date('Y') . '/monthly',
            $report,
            'report-' . date('Y-m') . '.xlsx'
        );
        echo "   Path: {$reportPath}\n";
        echo "   URL: " . $disk->url($reportPath) . "\n";
        $uploadedPaths[] = $reportPath;

        // Scenario 4: Image gallery
        echo "\n4. Image Gallery Upload\n";
        for ($i = 1; $i <= 3; $i++) {
            $img = UploadedFile::fake()->image("gallery-{$i}.jpg", 800, 600);
            $imgPath = 'gallery/images/' . $img->hashName();
            $disk->put($imgPath, $img->get());
            echo "   Image {$i} Path: {$imgPath}\n";
            $uploadedPaths[] = $imgPath;
        }

        // Verify all uploads
        echo "\n=== Verification ===\n";
        foreach ($uploadedPaths as $path) {
            $exists = $disk->exists($path);
            echo "✓ {$path}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
            $this->assertTrue($exists, "File {$path} should exist");
        }

        // Cleanup
        $disk->delete($uploadedPaths);
        echo "\n=== Cleanup Complete ===\n";
    }
}
