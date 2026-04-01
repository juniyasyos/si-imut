<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IamApplicationService;

class TestIamApplication extends Command
{
    protected $signature = 'iam:test {--endpoint= : Override IAM endpoint}';
    protected $description = 'Test IAM Applications Service';

    public function handle()
    {
        $this->info('Testing IAM Application Service...');
        $this->newLine();

        // Check config
        $this->info('Configuration:');
        $this->line('  IAM Enabled: ' . (config('iam.enabled') ? 'Yes' : 'No'));
        $this->line('  Local Route: /iam/user-applications');
        $this->newLine();

        // Check auth
        if (!auth()->check()) {
            $this->error('No authenticated user. Run in context with authenticated session.');
            return 1;
        }

        $user = auth()->user();
        $this->line('Authenticated User: ' . $user->name . ' (ID: ' . $user->id . ')');
        $this->newLine();

        $service = app(IamApplicationService::class);

        // Test raw response
        $this->info('Fetching raw applications from local endpoint...');
        try {
            $raw = $service->getAccessibleApplications();

            if ($raw === null) {
                $this->warn('  ⚠ Raw response is null');
            } else {
                $this->line('  ✓ Raw response received');
                $this->line('  Total accessible apps: ' . ($raw['total_accessible_apps'] ?? count($raw['applications'] ?? [])));
                $this->line('  Applications count: ' . count($raw['applications'] ?? []));
            }
        } catch (\Exception $e) {
            $this->error('  ✗ Error: ' . $e->getMessage());
        }
        $this->newLine();

        // Test formatted response
        $this->info('Fetching formatted applications...');
        try {
            $formatted = $service->getFormattedApplications();

            if (empty($formatted)) {
                $this->warn('  ⚠ No formatted applications');
            } else {
                $this->line('  ✓ Found ' . count($formatted) . ' application(s)');
                $this->newLine();

                $this->table(
                    ['App Key', 'Name', 'URL'],
                    collect($formatted)->map(fn($app) => [
                        $app['app_key'],
                        $app['name'],
                        $app['app_url'],
                    ])->toArray()
                );
            }
        } catch (\Exception $e) {
            $this->error('  ✗ Error: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('Test completed.');

        return 0;
    }
}
