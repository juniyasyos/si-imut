<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Tests\TestCase;

class MediaFolderPathTest extends TestCase
{
    /**
     * Test upload file ke nested folder mengikuti path hierarchy
     */
    public function test_upload_to_nested_folder_creates_correct_path(): void
    {
        $disk = Storage::disk('s3');

        // Get atau create folder IGD
        $igdFolder = Folder::firstOrCreate(
            ['collection' => 'igd'],
            [
                'name' => 'IGD',
                'description' => 'Unit IGD',
                'user_id' => 1,
                'user_type' => 'App\Models\User'
            ]
        );

        // Get atau create subfolder test
        $testFolder = Folder::firstOrCreate(
            ['collection' => 'test-nested', 'parent_id' => $igdFolder->id],
            [
                'name' => 'Test Nested',
                'parent_id' => $igdFolder->id,
                'user_id' => 1,
                'user_type' => 'App\Models\User'
            ]
        );

        echo "\n=== Folder Structure ===\n";
        echo "IGD Folder: {$igdFolder->id} - {$igdFolder->collection}\n";
        echo "Test Folder: {$testFolder->id} - {$testFolder->collection} (parent: {$testFolder->parent_id})\n";

        // Create temporary file
        $file = UploadedFile::fake()->image('test-nested-upload.jpg', 100, 100);
        $tmpPath = $file->store('temp', 'public');
        $fullPath = storage_path('app/public/' . $tmpPath);

        // Upload ke folder test (yang merupakan child dari IGD)
        $media = $testFolder
            ->addMedia($fullPath)
            ->usingFileName('test-nested-upload.jpg')
            ->toMediaCollection($testFolder->collection, 's3');

        echo "\n=== Media Info ===\n";
        echo "File: {$media->file_name}\n";
        echo "Collection: {$media->collection_name}\n";
        echo "Path: {$media->getPath()}\n";
        echo "URL: {$media->getUrl()}\n";

        // Verifikasi path mengandung igd/test
        $this->assertStringContainsString('igd', $media->getPath());
        $this->assertStringContainsString('test-nested', $media->getPath());

        // Verifikasi file ada di S3
        $this->assertTrue($disk->exists($media->getPath() . $media->file_name));

        // Check actual path di S3
        echo "\n=== S3 Files in igd/ ===\n";
        $igdFiles = $disk->allFiles('igd');
        foreach ($igdFiles as $f) {
            echo "- {$f}\n";
        }

        // Cleanup
        $media->delete();
        Storage::disk('public')->delete($tmpPath);

        $this->assertTrue(true);
    }

    /**
     * Test upload ke root folder
     */
    public function test_upload_to_root_folder_creates_simple_path(): void
    {
        $disk = Storage::disk('s3');

        // Get folder IGD (root level)
        $igdFolder = Folder::where('collection', 'igd')->whereNull('parent_id')->first();

        // Create temporary file
        $file = UploadedFile::fake()->create('test-root-upload.pdf', 100);
        $tmpPath = $file->store('temp', 'public');
        $fullPath = storage_path('app/public/' . $tmpPath);

        // Upload ke folder IGD (root level)
        $media = $igdFolder
            ->addMedia($fullPath)
            ->usingFileName('test-root-upload.pdf')
            ->toMediaCollection($igdFolder->collection, 's3');

        echo "\n=== Root Level Upload ===\n";
        echo "File: {$media->file_name}\n";
        echo "Collection: {$media->collection_name}\n";
        echo "Path: {$media->getPath()}\n";
        echo "URL: {$media->getUrl()}\n";

        // Verifikasi path hanya mengandung igd (tanpa parent)
        $this->assertStringStartsWith('igd/', $media->getPath());

        // Verifikasi file ada di S3
        $this->assertTrue($disk->exists($media->getPath() . $media->file_name));

        // Cleanup
        $media->delete();
        Storage::disk('public')->delete($tmpPath);

        $this->assertTrue(true);
    }
}
