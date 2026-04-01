#!/usr/bin/env php
<?php

/**
 * IAM App Switcher Debug Script
 * 
 * Jalankan di root siimut project:
 * php debug-iam-switcher.php
 * 
 * Akan menampilkan:
 * 1. Session data yang ada
 * 2. Config iam.apps yang ada
 * 3. Database aplikasi yang tersedia
 * 4. Data mana yang sebenarnya digunakan
 */

// Setup environment
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║           IAM APP SWITCHER DEBUG DIAGNOSTICS                 ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Create fake session for testing
$model = new \Illuminate\Session\Store(
    'debug-session',
    new \Illuminate\Session\Middleware\StartSession(app()),
    null,
    false
);

echo "📋 STEP 1: Check Session Data\n";
echo "────────────────────────────────────────────────────────────────\n";

// Try to get user
if (auth()->check()) {
    $user = auth()->user();
    echo "✅ User authenticated: {$user->name} (ID: {$user->id})\n";

    $iamSessionData = session('iam', []);
    if (!empty($iamSessionData)) {
        echo "✅ Session 'iam' data found:\n";
        foreach ($iamSessionData as $key => $value) {
            if ($key === 'roles' && is_array($value)) {
                echo "   • $key: [" . count($value) . " roles]\n";
                foreach ($value as $role) {
                    echo "      - {$role['name']} (slug: {$role['slug']})\n";
                }
            } else if ($key === 'access_token') {
                echo "   • $key: " . (strlen($value) > 50 ? substr($value, 0, 20) . '...' : $value) . "\n";
            } else {
                echo "   • $key: $value\n";
            }
        }
    } else {
        echo "❌ Session 'iam' data NOT found\n";
    }

    echo "\n   Session has 'iam.access_token'? " . (session()->has('iam.access_token') ? "✅ YES" : "❌ NO") . "\n";
    echo "   Session has 'iam.sub'? " . (session()->has('iam.sub') ? "✅ YES" : "❌ NO") . "\n";
} else {
    echo "❌ No authenticated user. Login terlebih dahulu via IAM\n";
    exit(1);
}

echo "\n\n📋 STEP 2: Check Config Data (config/iam.php)\n";
echo "────────────────────────────────────────────────────────────────\n";

$iamAppsConfig = config('iam.apps', []);
if (!empty($iamAppsConfig)) {
    echo "✅ Config 'iam.apps' found:\n";
    foreach ($iamAppsConfig as $appKey => $appData) {
        echo "\n   [$appKey]\n";
        if (isset($appData['name'])) {
            echo "   • name: " . ($appData['name'] ?? 'NOT SET') . "\n";
        } else {
            echo "   • name: ❌ NOT SET (will generate from app_key)\n";
        }
        if (isset($appData['description'])) {
            echo "   • description: " . (strlen($appData['description'] ?? '') > 50 ? substr($appData['description'], 0, 50) . '...' : ($appData['description'] ?? 'NOT SET')) . "\n";
        }
        echo "   • url: " . ($appData['url'] ?? 'NOT SET') . "\n";
    }
} else {
    echo "❌ Config 'iam.apps' is EMPTY or NOT SET\n";
}

echo "\n\n📋 STEP 3: Check IAM Database Data\n";
echo "────────────────────────────────────────────────────────────────\n";

try {
    $apps = DB::connection('mysql_iam')->table('iam_applications')
        ->where('enabled', true)
        ->get();

    if ($apps->isNotEmpty()) {
        echo "✅ Found " . $apps->count() . " enabled application(s) di IAM database:\n\n";
        foreach ($apps as $app) {
            echo "   [$app->app_key]\n";
            echo "   • name: " . (!empty($app->name) ? $app->name : "❌ EMPTY") . "\n";
            echo "   • description: " . (!empty($app->description) ? substr($app->description, 0, 60) . '...' : "❌ EMPTY") . "\n";
            echo "   • enabled: " . ($app->enabled ? "✅" : "❌") . "\n";
            echo "   • logo_url: " . (!empty($app->logo_url) ? "✅ " . $app->logo_url : "❌ NULL") . "\n";
            echo "   • redirect_uris: " . (json_encode(json_decode($app->redirect_uris ?? '[]'))) . "\n";

            // Check roles
            $roles = DB::connection('mysql_iam')->table('iam_roles')
                ->where('application_id', $app->id)
                ->where('is_active', true)
                ->get();

            if ($roles->isNotEmpty()) {
                echo "   • roles (" . $roles->count() . "):\n";
                foreach ($roles as $role) {
                    echo "      - {$role->name} (slug: {$role->slug})\n";
                    echo "        desc: " . (!empty($role->description) ? substr($role->description, 0, 50) . '...' : "❌ EMPTY") . "\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "❌ No enabled applications found in IAM database\n";
    }
} catch (\Exception $e) {
    echo "⚠️  Could not connect to IAM database: " . $e->getMessage() . "\n";
    echo "   Make sure .env has correct IAM database connection\n";
}

echo "\n\n📋 STEP 4: Data Source Analysis\n";
echo "════════════════════════════════════════════════════════════════\n";

$appKey = session('iam.app') ?? null;
if (!$appKey) {
    echo "❌ No app_key in session. Cannot determine data source.\n";
    exit(1);
}

echo "App Key: $appKey\n\n";

// Check priority
$hasToken = session()->has('iam.access_token');
$configHasName = isset($iamAppsConfig[$appKey]['name']);

echo "Data Source Priority:\n";
echo "  1️⃣  API (/api/users/applications) → requires token → ";
echo ($hasToken ? "✅ AVAILABLE" : "❌ NO TOKEN") . "\n";
echo "  2️⃣  Config (config/iam.apps) → ";
echo ($configHasName ? "✅ HAS NAME" : "❌ NO NAME") . "\n";
echo "  3️⃣  Default: ucfirst('$appKey') → \"" . ucfirst(str_replace('-', ' ', $appKey)) . "\"\n";

echo "\n🎯 WHICH WILL BE USED?\n";
if ($hasToken) {
    echo "   ✅ API DATA (dari /api/users/applications)\n";
    echo "      → Cek IAM database aplikasi punya nama lengkap?\n";
} else if ($configHasName) {
    echo "   ✅ CONFIG DATA (dari config/iam.apps)\n";
    echo "   Current: \"" . $iamAppsConfig[$appKey]['name'] . "\"\n";
} else {
    echo "   ❌ DEFAULT (generated)\n";
    echo "   Result: \"" . ucfirst(str_replace('-', ' ', $appKey)) . "\"\n";
    echo "   → Update config/iam.php dengan nama lengkap!\n";
}

echo "\n\n📋 STEP 5: Recommended Action\n";
echo "════════════════════════════════════════════════════════════════\n";

if ($hasToken && !empty($apps)) {
    $app = $apps->where('app_key', $appKey)->first();
    if ($app && empty($app->name)) {
        echo "❌ PROBLEM: API data dimulai, tapi nama aplikasi kosong di database IAM\n";
        echo "\n🔧 FIX:\n";
        echo "   UPDATE iam_applications SET\n";
        echo "   name = 'SIIMUT - Sistem Informasi Manajemen Indikator Mutu Terpadu',\n";
        echo "   description = 'Aplikasi manajemen indikator kinerja mutu rumah sakit dan unit kerja'\n";
        echo "   WHERE app_key = 'siimut';\n";
    } else if ($app && !empty($app->name)) {
        echo "✅ API data OK, nama sudah ada: \"" . $app->name . "\"\n";
        echo "   Tapi mungkin ada cache issue\n\n";
        echo "🔧 CLEAR CACHE:\n";
        echo "   php artisan cache:clear\n";
        echo "   php artisan view:clear\n";
        echo "   php artisan config:clear\n";
    }
} else if (!$hasToken && $configHasName) {
    echo "✅ Token tidak ada, tapi config sudah punya nama\n";
    echo "   Current: \"" . $iamAppsConfig[$appKey]['name'] . "\"\n";
} else if (!$hasToken) {
    echo "❌ PROBLEM: Fallback ke default (generated name)\n";
    echo "\n🔧 FIX: Update config/iam.php:\n";
    echo "   'iam' => [\n";
    echo "       'apps' => [\n";
    echo "           'siimut' => [\n";
    echo "               'name' => 'SIIMUT - Sistem Informasi Manajemen Indikator Mutu Terpadu',\n";
    echo "               'description' => 'Aplikasi manajemen indikator kinerja mutu rumah sakit dan unit kerja',\n";
    echo "               'url' => 'http://127.0.0.1:8088',\n";
    echo "           ]\n";
    echo "       ]\n";
    echo "   ]\n";
}

echo "\n\n📋 STEP 6: Check Recent Logs\n";
echo "════════════════════════════════════════════════════════════════\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    echo "Recent IAM debug logs:\n";
    exec("tail -30 $logFile | grep -i 'IamAppSwitcher DEBUG'", $debugLogs);
    if (!empty($debugLogs)) {
        foreach (array_slice($debugLogs, -10) as $line) {
            echo "  " . $line . "\n";
        }
    } else {
        echo "  (No recent IamAppSwitcher DEBUG logs found)\n";
    }
} else {
    echo "❌ Log file not found at $logFile\n";
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    END OF DIAGNOSTICS                        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";
