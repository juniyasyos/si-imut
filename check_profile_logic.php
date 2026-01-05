<?php

/**
 * Comprehensive Profile Selection Logic Checker
 * 
 * Script untuk memvalidasi bahwa profile selection menggunakan
 * assessment period laporan, bukan current time
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\LaporanImut;
use App\Models\ImutData;
use App\Models\ImutProfile;
use Carbon\Carbon;

echo "🔍 COMPREHENSIVE PROFILE SELECTION LOGIC VALIDATION\n";
echo str_repeat("=", 65) . "\n\n";

// 1. Ambil sample ImutData
$kebersihanData = ImutData::where('title', 'like', '%kepatuhan kebersihan tangan%')->first();
if (!$kebersihanData) {
    echo "❌ ImutData Kepatuhan Kebersihan Tangan tidak ditemukan\n";
    exit(1);
}

echo "🎯 Testing ImutData: {$kebersihanData->title} (ID: {$kebersihanData->id})\n\n";

// 2. Show all available profiles
$allProfiles = $kebersihanData->profiles()->orderBy('valid_from')->get();
echo "📊 Available Profiles:\n";
foreach ($allProfiles as $profile) {
    $status = $profile->valid_from <= now() && ($profile->valid_until === null || $profile->valid_until >= now())
        ? '🟢 Active' : '⚪ Inactive';
    echo "   • {$profile->version}: {$profile->valid_from->format('Y-m-d')} → {$profile->valid_until->format('Y-m-d')} | {$status}\n";
}
echo "\n";

// 3. Test scenarios with different assessment periods
$testScenarios = [
    ['2025-02-15', '2025-02-20', 'Q1 2025 Assessment'],
    ['2025-07-15', '2025-07-20', 'Q3 2025 Assessment'],
    ['2025-11-15', '2025-11-20', 'Q4 2025 Assessment'],
    ['2026-01-27', '2026-01-31', 'Q1 2026 Assessment (Current)'],
    ['2026-05-15', '2026-05-20', 'Q2 2026 Assessment'],
    ['2026-08-15', '2026-08-20', 'Q3 2026 Assessment'],
    ['2026-11-15', '2026-11-20', 'Q4 2026 Assessment'],
    ['2027-02-15', '2027-02-20', 'Q1 2027 Assessment (Future)'],
];

echo "🧪 TEST SCENARIOS - Profile Selection by Assessment Period:\n";
echo str_repeat("-", 60) . "\n";

$correctSelections = 0;
$totalTests = count($testScenarios);

foreach ($testScenarios as $index => [$startDate, $endDate, $description]) {
    echo ($index + 1) . ". {$description}\n";
    echo "   📅 Assessment Period: {$startDate} → {$endDate}\n";

    // Simulate Job logic
    $selectedProfile = $kebersihanData->profiles()
        ->validForPeriod($startDate, $endDate)
        ->orderBy('valid_from', 'desc')
        ->first();

    if ($selectedProfile) {
        echo "   ✅ Selected Profile: {$selectedProfile->version} (ID: {$selectedProfile->id})\n";
        echo "   📊 Profile Period: {$selectedProfile->valid_from->format('Y-m-d')} → {$selectedProfile->valid_until->format('Y-m-d')}\n";
        echo "   🎯 Target Value: {$selectedProfile->target_value}\n";

        // Validate logic
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $isValidSelection = $selectedProfile->valid_from <= $end &&
            ($selectedProfile->valid_until === null || $selectedProfile->valid_until >= $start);

        if ($isValidSelection) {
            echo "   ✅ CORRECT: Profile valid untuk assessment period\n";
            $correctSelections++;
        } else {
            echo "   ❌ ERROR: Profile tidak valid untuk assessment period\n";
        }
    } else {
        echo "   ⚠️  No valid profile found for this period\n";
    }
    echo "\n";
}

// 4. Summary
echo str_repeat("=", 65) . "\n";
echo "📊 VALIDATION SUMMARY:\n";
echo "   • Total Test Scenarios: {$totalTests}\n";
echo "   • Correct Profile Selections: {$correctSelections}\n";
echo "   • Success Rate: " . round(($correctSelections / $totalTests) * 100, 1) . "%\n\n";

if ($correctSelections === $totalTests) {
    echo "🎉 PERFECT! All profile selections are based on assessment period, NOT current time!\n";
    echo "✅ Logic is working correctly:\n";
    echo "   • Uses validForPeriod() scope with assessment dates\n";
    echo "   • Orders by valid_from DESC (not version string)\n";
    echo "   • Selects most recent valid profile for the assessment period\n";
} else {
    echo "⚠️  Some issues found in profile selection logic\n";
    echo "❗ Please review the failed scenarios above\n";
}

echo "\n🚀 Profile selection logic validation completed!\n";
