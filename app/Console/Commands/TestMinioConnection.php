<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestMinioConnection extends Command
{
    protected $signature = 'minio:test';
    protected $description = 'Test MinIO S3 connection and list buckets';

    public function handle(): int
    {
        $this->info('🔍 Testing MinIO S3 Connection...');
        $this->newLine();

        try {
            // 1. Check configuration
            $this->info('📋 Configuration:');
            $this->table(
                ['Setting', 'Value'],
                [
                    ['Access Key', config('filesystems.disks.s3.key') ? '✓ Set' : '✗ Missing'],
                    ['Secret Key', config('filesystems.disks.s3.secret') ? '✓ Set' : '✗ Missing'],
                    ['Endpoint', config('filesystems.disks.s3.endpoint')],
                    ['Bucket', config('filesystems.disks.s3.bucket')],
                    ['Region', config('filesystems.disks.s3.region')],
                    ['URL', config('filesystems.disks.s3.url')],
                ]
            );
            $this->newLine();

            // 2. Test basic connectivity
            $this->info('🔗 Testing Basic Connectivity...');
            $s3Client = Storage::disk('s3')->getClient();

            $result = $s3Client->headBucket([
                'Bucket' => config('filesystems.disks.s3.bucket'),
            ]);

            $this->info('✅ Successfully connected to MinIO!');
            $this->newLine();

            // 3. List buckets
            $this->info('📦 Available Buckets:');
            $buckets = $s3Client->listBuckets();
            foreach ($buckets['Buckets'] as $bucket) {
                $this->line('  • ' . $bucket['Name']);
            }
            $this->newLine();

            // 4. Test file operations
            $this->info('📝 Testing File Operations...');

            $testFile = 'test-connection-' . time() . '.txt';
            $testContent = 'MinIO Connection Test - ' . now();

            // Put
            Storage::disk('s3')->put($testFile, $testContent);
            $this->info("  ✓ Uploaded test file: {$testFile}");

            // Get
            $content = Storage::disk('s3')->get($testFile);
            if ($content === $testContent) {
                $this->info('  ✓ Downloaded and verified test file');
            }

            // Get URL
            $url = Storage::disk('s3')->url($testFile);
            $this->info("  ✓ Generated URL: {$url}");

            // Delete
            Storage::disk('s3')->delete($testFile);
            $this->info('  ✓ Deleted test file');

            $this->newLine();
            $this->info('✅ All MinIO tests passed!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ MinIO Connection Failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();

            $this->info('Troubleshooting:');
            $this->line('1. Ensure MinIO is running: docker-compose ps');
            $this->line('2. Check credentials in .env (AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY)');
            $this->line('3. Check endpoint: ' . config('filesystems.disks.s3.endpoint'));
            $this->line('4. Check bucket exists: ' . config('filesystems.disks.s3.bucket'));

            return self::FAILURE;
        }
    }
}
