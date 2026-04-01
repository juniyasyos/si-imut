<?php

/**
 * Verification script untuk IAM configuration dan session setup
 * Run dengan: php artisan tinker < verify-iam-config.php
 * atau bisa di-import dalam routes/console.php
 */

echo "\n========================================\n";
echo "IAM CLIENT SESSION CONFIGURATION VERIFICATION\n";
echo "========================================\n\n";

// 1. Check Session Driver
$sessionDriver = config('session.driver');
echo "1. Session Driver: {$sessionDriver}\n";
if ($sessionDriver === 'database') {
    echo "   ✅ CORRECT: Using database driver for IAM client compatibility\n";
} else {
    echo "   ❌ ERROR: Using {$sessionDriver} driver. Should be 'database' for IAM plugin\n";
}

// 2. Check Session Lifetime
$sessionLifetime = config('session.lifetime');
echo "\n2. Session Lifetime: {$sessionLifetime} minutes\n";
echo "   Session will expire after {$sessionLifetime} mins of inactivity\n";

// 3. Check IAM Token Verification Settings
echo "\n3. IAM Token Verification Settings:\n";

$verifyEachRequest = config('iam.verify_each_request');
echo "   - verify_each_request: " . ($verifyEachRequest ? 'true' : 'false');
if ($verifyEachRequest) {
    echo " ✅\n";
} else {
    echo " ❌ Should be true\n";
}

$verifyRemoteEachRequest = config('iam.verify_remote_each_request');
echo "   - verify_remote_each_request: " . ($verifyRemoteEachRequest ? 'true' : 'false');
if ($verifyRemoteEachRequest) {
    echo " ✅\n";
} else {
    echo " ❌ Should be true\n";
}

$attachVerifyMiddleware = config('iam.attach_verify_middleware');
echo "   - attach_verify_middleware: " . ($attachVerifyMiddleware ? 'true' : 'false');
if ($attachVerifyMiddleware) {
    echo " ✅\n";
} else {
    echo " ❌ Should be true\n";
}

// 4. Check IAM Endpoints
echo "\n4. IAM Server Endpoints:\n";
$baseUrl = config('iam.base_url');
$verifyEndpoint = config('iam.verify_endpoint');
echo "   - IAM Base URL: {$baseUrl}\n";
echo "   - Verify Endpoint: {$verifyEndpoint}\n";

// 5. Check Token Storage
echo "\n5. Token Storage Settings:\n";
$storeToken = config('iam.store_access_token_in_session');
echo "   - store_access_token_in_session: " . ($storeToken ? 'true' : 'false');
if ($storeToken) {
    echo " ✅\n";
} else {
    echo " ⚠️  Token won't be stored in session\n";
}

// 6. Test database sessions table exists
echo "\n6. Database Sessions Table:\n";
try {
    $sessionTableExists = \Illuminate\Support\Facades\DB::table(
        config('session.table', 'sessions')
    )->limit(1)->exists();

    if ($sessionTableExists || \Illuminate\Support\Facades\Schema::hasTable(config('session.table'))) {
        echo "   ✅ Sessions table exists\n";
    } else {
        echo "   ⚠️  Sessions table might not exist\n";
    }
} catch (\Exception $e) {
    echo "   ⚠️  Cannot verify sessions table: " . $e->getMessage() . "\n";
}

// 7. Summary
echo "\n========================================\n";
echo "SUMMARY - Session Expiration Behavior:\n";
echo "========================================\n";

if ($sessionDriver === 'database' && $verifyEachRequest && $verifyRemoteEachRequest && $attachVerifyMiddleware) {
    echo "\n✅ ALL CONFIGURATIONS CORRECT!\n\n";
    echo "How it works now:\n";
    echo "1. User logs in via SSO → IAM token stored in session\n";
    echo "2. Token expires after " . (config('iam.token_ttl') ?? 3600) . " seconds (configurable per app)\n";
    echo "3. VerifyIamToken middleware runs on EVERY request\n";
    echo "4. Middleware checks if token is expired\n";
    echo "5. If token expired:\n";
    echo "   - Session is automatically invalidated\n";
    echo "   - User redirected to login page\n";
    echo "6. If token valid:\n";
    echo "   - Session continues (max {$sessionLifetime} mins idle)\n";
    echo "\n✨ Result: Token expiration is properly enforced!\n";
} else {
    echo "\n❌ ISSUES FOUND - Please review configuration\n";
}

echo "\n";
