<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\User;

// Get all users
$allUsers = User::withTrashed()
    ->with('unitKerjas')
    ->select('id', 'name', 'email', 'nip', 'deleted_at')
    ->get();

// Group by name or nip (like the command does)
$groups = [];
foreach ($allUsers as $user) {
    $key = $user->name ?: $user->nip;
    if (!$key) continue;

    if (!isset($groups[$key])) {
        $groups[$key] = [];
    }

    $groups[$key][] = [
        'id' => $user->id,
        'name' => $user->name,
        'nip' => $user->nip,
        'uk_count' => $user->unitKerjas->count(),
        'deleted' => !is_null($user->deleted_at),
    ];
}

// Find duplicates
$dupGroups = array_filter($groups, fn($g) => count($g) > 1);

// Count what would be deleted
$toDelete = [];
$groupsWithNoUkDups = [];
foreach ($dupGroups as $key => $group) {
    $noUkInGroup = [];
    foreach ($group as $user) {
        if ($user['uk_count'] == 0) {
            $toDelete[] = $user;
            $noUkInGroup[] = $user['id'];
        }
    }
    if (!empty($noUkInGroup)) {
        $groupsWithNoUkDups[$key] = [
            'count' => count($group),
            'no_uk_ids' => $noUkInGroup,
            'all_ids' => array_column($group, 'id'),
        ];
    }
}

echo "=== ANALYSIS ===\n";
echo "Total users: " . $allUsers->count() . "\n";
echo "Total groups: " . count($groups) . "\n";
echo "Groups with duplicates: " . count($dupGroups) . "\n";
echo "Users WITHOUT unit kerja: " . $allUsers->filter(fn($u) => $u->unitKerjas->isEmpty())->count() . "\n";
echo "Duplicate groups with NO-UK users: " . count($groupsWithNoUkDups) . "\n";
echo "Users marked for deletion: " . count($toDelete) . "\n\n";

echo "=== FIRST 15 DUPLICATE GROUPS (with NO-UK users) ===\n";
$shown = 0;
foreach ($groupsWithNoUkDups as $key => $info) {
    if ($shown >= 15) break;
    $shown++;

    echo "\n[$key]\n";
    echo "  Total in group: " . $info['count'] . " | To delete (no UK): " . count($info['no_uk_ids']) . "\n";
    echo "  All IDs: " . implode(',', $info['all_ids']) . "\n";
    echo "  Delete IDs: " . implode(',', $info['no_uk_ids']) . "\n";
}
