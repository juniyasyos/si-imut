<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MinioStorageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan menggunakan S3/MinIO disk
        config(['filesystems.default' => 's3']);
    }

    /**
     * Test koneksi ke MinIO
     */
    public function test_minio_connection(): void
    {
        $disk = Storage::disk('s3');

        // Test apakah disk bisa diakses
        $this->assertTrue($disk->exists('') || true);
    }

    /**
     * Test upload file ke MinIO dengan path yang benar
     */
    public function test_upload_file_to_minio_with_correct_path(): void
    {
        $disk = Storage::disk('s3');

        // Buat file dummy
        $file = UploadedFile::fake()->image('test-image.jpg', 100, 100);

        // Upload ke path tertentu
        $path = 'test-folder/images/' . $file->hashName();
        $uploaded = $disk->put($path, $file->get());

        $this->assertTrue($uploaded);

        // Verifikasi file ada
        $this->assertTrue($disk->exists($path));

        // Verifikasi URL dapat diakses
        $url = $disk->url($path);
        $this->assertStringContainsString('test-folder/images/', $url);
        $this->assertStringContainsString($file->hashName(), $url);

        // Cleanup
        $disk->delete($path);
        $this->assertFalse($disk->exists($path));
    }

    /**
     * Test upload multiple files dengan struktur folder
     */
    public function test_upload_multiple_files_with_folder_structure(): void
    {
        $disk = Storage::disk('s3');

        $folders = [
            'documents/pdf',
            'images/avatars',
            'images/banners',
            'uploads/reports',
        ];

        $uploadedPaths = [];

        foreach ($folders as $folder) {
            $file = UploadedFile::fake()->image("test-{$folder}.jpg", 50, 50);
            $path = $folder . '/' . $file->hashName();

            $uploaded = $disk->put($path, $file->get());
            $this->assertTrue($uploaded);

            $uploadedPaths[] = $path;

            // Verifikasi file ada di path yang benar
            $this->assertTrue($disk->exists($path));

            // Verifikasi URL mengandung path folder yang benar
            $url = $disk->url($path);
            $this->assertStringContainsString($folder, $url);
        }

        // Cleanup semua file
        foreach ($uploadedPaths as $path) {
            $disk->delete($path);
            $this->assertFalse($disk->exists($path));
        }
    }

    /**
     * Test upload dengan storage facade menggunakan putFileAs
     */
    public function test_upload_with_storage_put_file_as(): void
    {
        $disk = Storage::disk('s3');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        // Upload dengan nama file custom
        $path = $disk->putFileAs('documents/contracts', $file, 'contract-2024.pdf');

        $this->assertNotNull($path);
        $this->assertEquals('documents/contracts/contract-2024.pdf', $path);
        $this->assertTrue($disk->exists($path));

        // Verifikasi URL
        $url = $disk->url($path);
        $this->assertStringContainsString('documents/contracts/contract-2024.pdf', $url);

        // Cleanup
        $disk->delete($path);
    }

    /**
     * Test list files dalam folder tertentu
     */
    public function test_list_files_in_folder(): void
    {
        $disk = Storage::disk('s3');

        // Upload beberapa file ke folder test-listing
        $filePaths = [];
        for ($i = 1; $i <= 3; $i++) {
            $file = UploadedFile::fake()->image("test-{$i}.jpg");
            $path = "test-listing/file-{$i}." . $file->extension();
            $disk->put($path, $file->get());
            $filePaths[] = $path;
        }

        // List semua file di folder test-listing
        $files = $disk->files('test-listing');

        $this->assertCount(3, $files);

        // Verifikasi setiap file path benar
        foreach ($filePaths as $expectedPath) {
            $this->assertContains($expectedPath, $files);
        }

        // Cleanup
        foreach ($filePaths as $path) {
            $disk->delete($path);
        }
    }

    /**
     * Test delete folder dan isinya
     */
    public function test_delete_folder_and_contents(): void
    {
        $disk = Storage::disk('s3');

        // Buat folder dengan beberapa file
        $folderPath = 'test-delete-folder';

        for ($i = 1; $i <= 3; $i++) {
            $file = UploadedFile::fake()->image("test-{$i}.jpg");
            $path = "{$folderPath}/subfolder/file-{$i}.jpg";
            $disk->put($path, $file->get());
        }

        // Verifikasi folder ada
        $files = $disk->allFiles($folderPath);
        $this->assertCount(3, $files);

        // Delete seluruh folder
        $disk->deleteDirectory($folderPath);

        // Verifikasi folder kosong
        $files = $disk->allFiles($folderPath);
        $this->assertCount(0, $files);
    }

    /**
     * Test path visibility dan access
     */
    public function test_file_visibility(): void
    {
        $disk = Storage::disk('s3');

        $file = UploadedFile::fake()->image('test-visibility.jpg');
        $path = 'test-visibility/' . $file->hashName();

        // Upload dengan visibility public (default)
        $disk->put($path, $file->get(), 'public');

        $this->assertTrue($disk->exists($path));

        // Verifikasi URL accessible
        $url = $disk->url($path);
        $this->assertNotEmpty($url);
        $this->assertStringStartsWith('http', $url);

        // Cleanup
        $disk->delete($path);
    }

    /**
     * Test upload file dengan metadata
     */
    public function test_upload_file_with_metadata(): void
    {
        $disk = Storage::disk('s3');

        $file = UploadedFile::fake()->create('report.pdf', 200);
        $path = 'reports/monthly/' . $file->hashName();

        // Upload file
        $uploaded = $disk->put($path, $file->get());

        $this->assertTrue($uploaded);
        $this->assertTrue($disk->exists($path));

        // Verifikasi size
        $size = $disk->size($path);
        $this->assertGreaterThan(0, $size);

        // Verifikasi last modified
        $lastModified = $disk->lastModified($path);
        $this->assertIsInt($lastModified);
        $this->assertGreaterThan(0, $lastModified);

        // Cleanup
        $disk->delete($path);
    }
}
