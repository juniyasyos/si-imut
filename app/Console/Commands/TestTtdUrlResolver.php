<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SignatoryService;
use Illuminate\Console\Command;

class TestTtdUrlResolver extends Command
{
    protected $signature = 'ttd:test {--user-id=1}';
    protected $description = 'Test TTD URL resolver (IAM or local)';

    public function handle(): int
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("❌ User #{$userId} not found");
            return self::FAILURE;
        }

        $this->info("🔍 Testing TTD URL Resolver for User: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // 1. Show configuration
        $this->info('📋 Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['IAM Enabled', config('iam.enabled') ? '✓ Yes' : '✗ No'],
                ['IAM Base URL', config('iam.base_url')],
                ['User TTD URL (DB)', $user->ttd_url ?: 'Not set'],
            ]
        );
        $this->newLine();

        // 2. Test local TTD (if exists)
        if ($user->ttd_url) {
            $this->info('🔗 Testing TTD URL Resolution:');

            try {
                $service = app(SignatoryService::class);
                $ttdUrl = $service->getTtdUrl($user);

                if ($ttdUrl) {
                    $this->info("✅ TTD URL resolved successfully!");
                    $this->table(
                        ['Aspect', 'Value'],
                        [
                            ['Method', config('iam.enabled') ? 'IAM API' : 'Local (S3/Public)'],
                            ['URL', $ttdUrl],
                            ['Type', preg_match('#^https?://#', $ttdUrl) ? 'Presigned/Absolute' : 'Relative'],
                            ['Accessible', $this->checkUrlAccessible($ttdUrl) ? '✓ Yes' : '⚠ Maybe'],
                        ]
                    );
                } else {
                    $this->warn('⚠ TTD URL could not be resolved');
                    $this->info('Possible causes:');
                    $this->line('  • File not found in MinIO bucket');
                    $this->line('  • IAM endpoint unreachable');
                    $this->line('  • Invalid auth token');
                }
                $this->newLine();

                // 3. Test accessor
                $this->info('🔐 Testing Model Accessor:');
                $accessorUrl = $user->ttd_presigned_url;
                $this->info("  \$user->ttd_presigned_url: " . ($accessorUrl ? '✓ Works' : '✗ Failed'));
                if ($accessorUrl) {
                    $this->line("  URL: {$accessorUrl}");
                }
                $this->newLine();

                // 4. Test old method (backward compatibility)
                $this->info('🔄 Testing Deprecated Method (Backward Compatibility):');
                $oldUrl = $user->getFilamentTtdUrl();
                $this->info("  \$user->getFilamentTtdUrl(): " . ($oldUrl ? '✓ Works' : '✗ Failed'));
                if ($oldUrl) {
                    $this->line("  URL: {$oldUrl}");
                }

                return self::SUCCESS;
            } catch (\Exception $e) {
                $this->error("❌ Error during resolution: {$e->getMessage()}");
                $this->line("Stack trace: {$e->getTraceAsString()}");
                return self::FAILURE;
            }
        } else {
            $this->warn("⚠ User #{$userId} has no TTD file configured");
            $this->info("To test with TTD, upload one in Filament or set ttd_url in database");
            return self::SUCCESS;
        }
    }

    private function checkUrlAccessible(string $url): bool
    {
        try {
            $headers = @get_headers($url, 1);
            return is_array($headers) && strpos($headers[0], '200') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
