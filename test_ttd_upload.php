<?php

use Illuminate\Support\Facades\Storage;

// Test MinIO TTD Upload

echo "\n=== Testing MinIO TTD Upload ===\n\n";

// 1. Cek Konfigurasi S3
echo "1. Konfigurasi MinIO:\n";
echo "   Endpoint: " . config('filesystems.disks.s3.endpoint') . "\n";
echo "   Bucket: " . config('filesystems.disks.s3.bucket') . "\n";
echo "   Access Key: " . config('filesystems.disks.s3.key') . "\n";
echo "   Region: " . config('filesystems.disks.s3.region') . "\n";
echo "   Use Path Style: " . (config('filesystems.disks.s3.use_path_style_endpoint') ? 'true' : 'false') . "\n\n";

// 2. Test Koneksi
echo "2. Testing Koneksi ke MinIO:\n";
try {
    $disk = Storage::disk('s3');

    // Coba list files untuk test koneksi
    $files = $disk->listContents('ttd');
    echo "   ✓ Koneksi berhasil\n";
    echo "   Files di directory 'ttd':\n";

    if (empty($files) || count($files) == 0) {
        echo "      (Kosong)\n";
    } else {
        foreach ($files as $file) {
            echo "      - " . $file['path'] . "\n";
        }
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Koneksi gagal: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. Test Upload
echo "3. Testing Upload TTD:\n";
try {
    // Buat test image (transparent PNG)
    $testImagePath = '/tmp/test-ttd.png';
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
        echo "   ✓ Upload berhasil\n";
        echo "   Path: " . $path . "\n";

        // Cek URL yang di-generate
        $url = Storage::disk('s3')->url($path);
        echo "   URL: " . $url . "\n\n";

        // Cek apakah file bisa diakses
        if (Storage::disk('s3')->exists($path)) {
            echo "   ✓ File terverifikasi di MinIO\n\n";
        } else {
            echo "   ✗ File tidak ditemukan di MinIO setelah upload\n\n";
        }
    } else {
        echo "   ✗ Upload gagal\n\n";
    }

    @unlink($testImagePath);
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n\n";
}

// 4. Test Upload dengan array (seperti Filament)
echo "4. Testing Upload dengan UploadedFile:\n";
try {
    $testImagePath = '/tmp/test-ttd-2.png';
    $imageData = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    );
    file_put_contents($testImagePath, $imageData);

    // Gunakan Storage::putFile seperti Filament FileUpload
    $path = Storage::disk('s3')->putFile('ttd', new \Illuminate\Http\File($testImagePath), 'public');

    if ($path) {
        echo "   ✓ putFile berhasil\n";
        echo "   Path: " . $path . "\n";
        echo "   URL: " . Storage::disk('s3')->url($path) . "\n\n";
    } else {
        echo "   ✗ putFile gagal\n\n";
    }

    @unlink($testImagePath);
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n\n";
}

// 5. List semua file di bucket
echo "5. Semua File di Bucket 'siimut':\n";
try {
    $disk = Storage::disk('s3');
    $allFiles = $disk->listContents('/', true);

    if (empty($allFiles) || count($allFiles) == 0) {
        echo "   (Bucket kosong)\n";
    } else {
        foreach ($allFiles as $file) {
            if (!$file['type']) continue; // Skip directories
            echo "   - " . $file['path'] . " (" . ($file['size'] ?? 0) . " bytes)\n";
        }
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Test Selesai ===\n";
