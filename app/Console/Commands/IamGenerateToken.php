<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IamGenerateToken extends Command
{
    protected $signature = 'iam:generate-token {--user-id=1 : User ID to generate token for}';
    protected $description = 'Generate IAM access token for development testing';

    public function handle()
    {
        $userId = $this->option('user-id');
        $iamBaseUrl = config('iam.base_url', 'http://127.0.0.1:8010');

        $this->info('Generating IAM access token...');
        $this->line("IAM Base URL: {$iamBaseUrl}");
        $this->line("User ID: {$userId}");
        $this->newLine();

        try {
            // Coba endpoint untuk generate token
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post($iamBaseUrl . '/api/development/generate-token', [
                    'user_id' => $userId,
                    'app_key' => config('iam.app_key', 'siimut'),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? $data['token'] ?? null;

                if ($token) {
                    $this->info('✓ Token generated successfully!');
                    $this->newLine();
                    $this->line("Token:");
                    $this->line("<info>$token</info>");
                    $this->newLine();

                    $this->line('Add to .env:');
                    $this->line("<comment>IAM_MOCK_TOKEN=$token</comment>");
                    $this->newLine();

                    $this->info('Token is valid for development testing.');
                    return 0;
                }
            }

            $this->error('Failed to generate token: ' . $response->body());
            return 1;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());

            $this->newLine();
            $this->warn('Alternative: Use direct cURL to test endpoint');
            $this->line("curl -X POST {$iamBaseUrl}/api/development/generate-token \\");
            $this->line("  -H 'Content-Type: application/json' \\");
            $this->line("  -d '{\"user_id\": $userId, \"app_key\": \"siimut\"}'");

            return 1;
        }
    }
}
