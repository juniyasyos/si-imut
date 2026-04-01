<?php

/**
 * Test UserApplicationsService in SIIMUT with HTTP simulation
 * 
 * This simulates a real HTTP request scenario where SIIMUT calls the IAM API
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Juniyasyos\IamClient\Services\UserApplicationsService;

echo "═══════════════════════════════════════════════════════════════\n";
echo "Testing UserApplicationsService with Real API Calls\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Use pre-generated token from IAM server
$testToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiZWNmZmIyMTEyNzdhZjFlZmRlMTc5YzE5ZDRiZTU5ZDllOWQ1MGE4ODdlMjhhMTBiM2RmMzY1MGE0YmRkYWNmNDhhNGQ2NmY0Zjk1YTdkY2EiLCJpYXQiOjE3NzUwNjc3MzMuOTQ0ODMyLCJuYmYiOjE3NzUwNjc3MzMuOTQ0ODM2LCJleHAiOjE4MDY2MDM3MzMuOTEwNDcxLCJzdWIiOiIxIiwic2NvcGVzIjpbIioiXX0.UvSI2tTM0LbRC7VanEOXXlW5O8O_3jVPMQ3WfTHEZ0kOpwGpdFSI8zlQWHaIc-u6ptw6Qk0Qr4x7j4GGW7vjJeglYIlXoTY326Cp8SWVyY8uitnVrOoEb5WevMpQIgb054AvZdjEqcylJNm5dJ9ARC9kBzPaJukoqseHDTwL8UqAtQ3CK5t7ZSwO-eNe9wHAc_tZotT7r7wp7ai4gEhZOgPIalD-U-6ZJlfdPMvBM9bbyZfwBsu4kOInTA6bpr6z-P6mEGnBuM161mt2TJ4rF-tOiRDRgMPxXrLjvU0FzQtUzCJzSpoPCHb6qUcmYPFGX4vxHRoDxed7dd3FRwHGA9vkSeY1EpMDk4lZWs8m31icIGIQkv8sapTZRqzlLmBoyreys6_y3XHXkQY_Q2Z9TrHovbm8JDwQIqL3qZQjDcbIMGozJbfwCYf0M8GE495syweRm4E99EMAsIpqSnSqVpbcs7byHyLAwdF8aKsf0dceF5r1_R8w1jnJb8l-w5NUjoLizbvMxmpkr2xZtkhIcqwJH2RTBeg6kNTtWYN0ERM1GeQ-5bUFGXl34YprZwd6mRDHAoBkBueUcGGzm7-CDRmVL5LNk3RfM56lLtbsJYSHDpHHlcrmksv_IQePxB46sjzI5_TclDN9NBSnSWmyj4u58e_Ci9FGsi10TCJqqEw';

// Simulate session with token
Session::put('iam.access_token', $testToken);
Session::put('iam.user_id', 1);

echo "📍 Session Setup:\n";
echo "   Token: ✓ Set\n";
echo "   User ID: ✓ 1\n\n";

$service = app(UserApplicationsService::class);

// Test 1: getApplications
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 1: UserApplicationsService::getApplications()\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $result = $service->getApplications();

    echo "Response received:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // Validate
    if (isset($result['error'])) {
        echo "⚠️  Error: " . $result['error'] . "\n";
        echo "   Message: " . $result['message'] . "\n";
    } else {
        echo "✅ SUCCESS!\n";
        echo "   User ID: " . ($result['user_id'] ?? '-') . "\n";
        echo "   Total Apps: " . ($result['total_accessible_apps'] ?? 0) . "\n";
        if (!empty($result['applications'])) {
            echo "   Apps Found:\n";
            foreach ($result['applications'] as $app) {
                echo "     • " . $app['name'] . "\n";
                echo "       Key: " . $app['app_key'] . "\n";
                echo "       URL: " . $app['app_url'] . "\n";
                echo "       Roles: " . $app['roles_count'] . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: getApplicationsDetail
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 2: UserApplicationsService::getApplicationsDetail()\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $result = $service->getApplicationsDetail();

    echo "Response received (summary):\n";
    if (isset($result['error'])) {
        echo "⚠️  Error: " . $result['error'] . "\n";
        echo "   Message: " . $result['message'] . "\n";
    } else {
        echo "✅ SUCCESS!\n";
        echo "   Total Apps: " . ($result['total_accessible_apps'] ?? 0) . "\n";
        echo "   User Profiles: " . count($result['user_profiles'] ?? []) . "\n";

        if (!empty($result['applications'])) {
            echo "   Apps with Metadata:\n";
            foreach ($result['applications'] as $app) {
                echo "     • " . $app['name'] . "\n";
                echo "       Has metadata: " . (isset($app['metadata']) ? 'Yes' : 'No') . "\n";
                if (isset($app['metadata'])) {
                    echo "       Logo available: " . ($app['metadata']['logo']['available'] ? 'Yes' : 'No') . "\n";
                    echo "       Timestamps: " . (isset($app['metadata']['created_at']) ? 'Yes' : 'No') . "\n";
                }
                echo "       Roles: " . $app['roles_count'] . "\n";
            }
        }

        // Show full response if small
        if (strlen(json_encode($result)) < 2000) {
            echo "\nFull Response:\n";
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: getApplicationByKey
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 3: UserApplicationsService::getApplicationByKey('siimut')\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $result = $service->getApplicationByKey('siimut');

    if ($result === null) {
        echo "⚠️  Application not found (returns null)\n";
        echo "   This is expected if app data is empty\n";
    } else {
        echo "✅ SUCCESS!\n";
        echo "   Found: " . $result['name'] . "\n";
        echo "   Key: " . $result['app_key'] . "\n";
        echo "   Status: " . $result['status'] . "\n";
        echo "   URL: " . $result['app_url'] . "\n";
        echo "   Roles: " . $result['roles_count'] . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Debug methods
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 4: Debug Methods\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $debug = $service->debugAll();
    echo "✅ debugAll() returned successfully\n";
    echo "   Has session info: " . (isset($debug['session']) ? 'Yes' : 'No') . "\n";
    echo "   Has endpoints: " . (isset($debug['endpoints']) ? 'Yes' : 'No') . "\n";
    echo "   Has basic_endpoint: " . (isset($debug['basic_endpoint']) ? 'Yes' : 'No') . "\n";
    echo "   Has detail_endpoint: " . (isset($debug['detail_endpoint']) ? 'Yes' : 'No') . "\n";
    echo "   Execution time: " . ($debug['execution_time_ms'] ?? '?') . "ms\n";
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "✓ All Tests Completed\n";
echo "═══════════════════════════════════════════════════════════════\n";
