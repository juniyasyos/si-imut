<?php

/**
 * Test UserApplicationsService in SIIMUT client app
 * 
 * This script tests the UserApplicationsService with mocked IAM session
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Session;
use Juniyasyos\IamClient\Services\UserApplicationsService;

echo "═══════════════════════════════════════════════════════════════\n";
echo "Testing UserApplicationsService in SIIMUT Client App\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Mock Session data from IAM (simulating logged-in user from IAM)
// In real HTTP request, this comes from IAM login
$mockIamToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxIiwibmFtZSI6ImFkbWluIiwibmlwIjoiMDAwMC4wMDAwMCIsImVtYWlsIjoiYWRtaW5AZXhhbXBsZS5jb20iLCJpYXQiOjE2NTc0MDAwMDB9.ABC123';
$mockUserId = 1;

// Simulate IAM session
Session::put('iam.access_token', $mockIamToken);
Session::put('iam.user_id', $mockUserId);

echo "📍 Session Configuration:\n";
echo "   IAM Token: " . (Session::has('iam.access_token') ? '✓ Set' : '✗ Missing') . "\n";
echo "   User ID:   " . (Session::has('iam.user_id') ? '✓ ' . Session::get('iam.user_id') : '✗ Missing') . "\n\n";

// Test Service Methods
$service = app(UserApplicationsService::class);

echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 1: getApplications()\n";
echo "═══════════════════════════════════════════════════════════════\n";

try {
    $result = $service->getApplications();
    echo "Status: " . ($result['source'] ?? 'unknown') . "\n";
    if (isset($result['error'])) {
        echo "Error: " . $result['error'] . "\n";
        echo "Message: " . $result['message'] . "\n";
    } else {
        echo "User ID: " . ($result['user_id'] ?? '-') . "\n";
        echo "Total Apps: " . ($result['total_accessible_apps'] ?? 0) . "\n";
        if (!empty($result['applications'])) {
            echo "\nApplications:\n";
            foreach ($result['applications'] as $app) {
                echo "  • " . $app['name'] . " (" . $app['app_key'] . ")\n";
                echo "    Status: " . $app['status'] . "\n";
                echo "    URL: " . $app['app_url'] . "\n";
                echo "    Roles: " . $app['roles_count'] . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 2: getApplicationsDetail()\n";
echo "═══════════════════════════════════════════════════════════════\n";

try {
    $result = $service->getApplicationsDetail();
    echo "Status: " . ($result['source'] ?? 'unknown') . "\n";
    if (isset($result['error'])) {
        echo "Error: " . $result['error'] . "\n";
        echo "Message: " . $result['message'] . "\n";
    } else {
        echo "Total Apps: " . ($result['total_accessible_apps'] ?? 0) . "\n";
        if (!empty($result['applications'])) {
            echo "\nApplications (Detailed):\n";
            foreach ($result['applications'] as $app) {
                echo "  • " . $app['name'] . " (" . $app['app_key'] . ")\n";
                if (isset($app['metadata'])) {
                    echo "    Logo: " . ($app['metadata']['logo']['available'] ? 'Available' : 'Not available') . "\n";
                    if (isset($app['metadata']['urls'])) {
                        echo "    URLs: " . json_encode($app['metadata']['urls']) . "\n";
                    }
                }
                if (isset($app['user_profiles']) && !empty($app['user_profiles'])) {
                    echo "    User Profiles: " . implode(', ', array_column($app['user_profiles'], 'name')) . "\n";
                }
            }
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 3: getApplicationByKey('siimut')\n";
echo "═══════════════════════════════════════════════════════════════\n";

try {
    $result = $service->getApplicationByKey('siimut');
    if (is_null($result)) {
        echo "Application not found (null)\n";
    } else {
        echo "Found: " . $result['name'] . "\n";
        echo "Status: " . $result['status'] . "\n";
        echo "URL: " . $result['app_url'] . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 4: debugAll()\n";
echo "═══════════════════════════════════════════════════════════════\n";

try {
    $debug = $service->debugAll();
    echo "Debug Info:\n";
    echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "✓ Testing Complete\n";
echo "═══════════════════════════════════════════════════════════════\n";
