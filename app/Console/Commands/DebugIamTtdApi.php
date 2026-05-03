<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DebugIamTtdApi extends Command
{
    protected $signature = 'ttd:debug-iam {--user-id=1}';
    protected $description = 'Debug IAM TTD API call';

    public function handle(): int
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("❌ User #{$userId} not found");
            return self::FAILURE;
        }

        $this->info("🔍 Debugging IAM TTD API for: {$user->name}");
        $this->newLine();

        // 1. Check IAM config
        $this->info('📋 IAM Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['IAM Enabled', config('iam.enabled') ? 'Yes' : 'No'],
                ['IAM Base URL', config('iam.base_url')],
                ['JWT Secret', config('iam.jwt_secret') ? 'Set' : 'Not set'],
            ]
        );
        $this->newLine();

        // 2. Check auth token
        $this->info('🔐 Authentication Token:');
        $token = auth()->user()?->currentAccessToken()?->token;
        $this->line("  From currentAccessToken(): " . ($token ? '✓ Found' : '✗ Not found'));

        $requestToken = request()->bearerToken();
        $this->line("  From request header: " . ($requestToken ? '✓ Found' : '✗ Not found'));

        if (!$token && !$requestToken) {
            $this->warn("⚠ No auth token found! This is required for IAM API call.");
            $this->info("In production, ensure user is authenticated via SSO.");
            $this->newLine();
            $this->info("Generating test token for debugging...");

            // Try to generate token manually
            try {
                $token = $this->generateTestToken($user);
                if ($token) {
                    $this->info("✓ Generated test token: " . substr($token, 0, 50) . "...");
                }
            } catch (\Exception $e) {
                $this->error("Could not generate token: {$e->getMessage()}");
                return self::FAILURE;
            }
        } else {
            $token = $token ?? $requestToken;
            $this->info("✓ Using token: " . substr($token, 0, 50) . "...");
        }
        $this->newLine();

        // 3. Make API call
        if (!$token) {
            $this->error("❌ Cannot proceed without token");
            return self::FAILURE;
        }

        $this->info('🌐 Calling IAM API:');
        $iamBaseUrl = rtrim(config('iam.base_url'), '/');
        $endpoint = "{$iamBaseUrl}/api/users/{$user->id}/ttd-url";

        $this->line("  Endpoint: {$endpoint}");
        $this->line("  Method: GET");
        $this->line("  Token: " . substr($token, 0, 30) . "...");
        $this->newLine();

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->get($endpoint);

            $this->info('📊 Response:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Status Code', $response->status()],
                    ['Successful', $response->successful() ? 'Yes' : 'No'],
                    ['Headers', json_encode($response->headers())],
                ]
            );
            $this->newLine();

            $this->info('📝 Response Body:');
            $body = $response->json();
            $this->line(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->newLine();

            if ($response->successful() && isset($body['url'])) {
                $this->info('✅ TTD URL retrieved successfully!');
                $this->line("URL: {$body['url']}");
                return self::SUCCESS;
            } else {
                $this->warn('⚠ Response received but no TTD URL in body');
                return self::SUCCESS;
            }
        } catch (\Exception $e) {
            $this->error("❌ API Call Failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function generateTestToken(User $user): ?string
    {
        try {
            // Try using TokenBuilder if available
            if (class_exists('App\Domain\Iam\Services\TokenBuilder')) {
                $builder = app('App\Domain\Iam\Services\TokenBuilder');
                return $builder->buildTokenForUser($user);
            }

            // Try Sanctum
            if (method_exists($user, 'createToken')) {
                $tokenObj = $user->createToken('test');
                return $tokenObj->plainTextToken;
            }

            return null;
        } catch (\Exception $e) {
            $this->warn("Token generation error: {$e->getMessage()}");
            return null;
        }
    }
}
