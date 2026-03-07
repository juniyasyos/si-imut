<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteDuplicateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-duplicates 
                            {--force : Jalankan tanpa konfirmasi}
                            {--dry-run : Preview tanpa benar-benar menghapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus PERMANEN user duplicate (hard delete), prioritaskan keep yang punya NIP atau nama, skip admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Scanning untuk duplicate users berdasarkan nama+NIP...');

        // Get duplicates grouped by name + NIP
        $duplicatesByNameNip = $this->getDuplicatesByNameAndNip();

        if (empty($duplicatesByNameNip)) {
            $this->info('✅ Tidak ada user yang duplicate ditemukan.');
            return 0;
        }

        // Filter: prioritize keep users with valid NIP+name, skip admin
        $usersToDelete = $this->filterUsersToDelete($duplicatesByNameNip);

        if (empty($usersToDelete)) {
            $this->info('✅ Tidak ada user yang memenuhi kriteria penghapusan.');
            return 0;
        }

        // Display summary
        $this->displaySummary($duplicatesByNameNip, $usersToDelete);

        // Show verbose: display all duplicate groups found
        if ($this->option('dry-run')) {
            $this->displayAllDuplicateGroups($duplicatesByNameNip);
        }

        // Ask for confirmation if not dry-run and not force
        if (!$dryRun && !$force) {
            if (!$this->confirm("Apakah Anda yakin untuk menghapus {$this->count($usersToDelete)} user?")) {
                $this->warn('Dibatalkan.');
                return 0;
            }
        }

        // Perform deletion or dry run
        if ($dryRun) {
            $this->info("\n📋 DRY RUN MODE - Data tidak akan dihapus");
            $this->displayUsersToDelete($usersToDelete);
            return 0;
        }

        $deletedCount = $this->deleteUsers($usersToDelete);
        $this->info("\n✅ Berhasil menghapus $deletedCount user.");

        return 0;
    }

    /**
     * Get duplicate users by name + NIP combination (OR logic)
     */
    private function getDuplicatesByNameAndNip(): array
    {
        // Get all users with name or nip (include soft-deleted to see history)
        $allUsers = User::withTrashed()
            ->where(function ($query) {
                $query->whereNotNull('name')
                    ->orWhereNotNull('nip');
            })
            ->with('unitKerjas', 'roles')
            ->select('id', 'name', 'email', 'nip', 'created_at', 'deleted_at')
            ->get();

        if ($allUsers->isEmpty()) {
            return [];
        }

        // Group by name OR nip combination
        $groups = [];
        foreach ($allUsers as $user) {
            // Use name as key if available, otherwise nip
            $key = !empty($user->name) ? $user->name : (!empty($user->nip) ? $user->nip : 'unknown');

            if (!isset($groups[$key])) {
                $groups[$key] = [];
            }

            $groups[$key][] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip,
                'created_at' => $user->created_at,
                'deleted_at' => $user->deleted_at,
                'unit_kerja_count' => $user->unitKerjas->count(),
                'is_admin' => $user->hasAnyRole(['admin', 'super_admin']),
            ];
        }

        // Keep only groups with duplicates
        $result = [];
        foreach ($groups as $key => $group) {
            if (count($group) > 1) {
                $result[$key] = $group;
            }
        }

        return $result;
    }

    /**
     * Filter users to delete:
     * - Skip admin users
     * - Skip users already deleted (soft delete)
     * - Prioritize keeping users with valid NIP and name
     * - Delete the rest (duplicates)
     */
    private function filterUsersToDelete(array $groups): array
    {
        $toDelete = [];

        foreach ($groups as $group) {
            // Remove admin users from consideration
            $nonAdminUsers = array_filter($group, function ($user) {
                return !$user['is_admin'];
            });

            if (empty($nonAdminUsers)) {
                continue;
            }

            // Include both active and soft-deleted users
            // Sort by created_at to keep newest, delete older ones (including soft-deleted)
            // First, separate by data completeness

            // Separate users with NIP or name vs those without both
            $completeUsers = array_filter($nonAdminUsers, function ($user) {
                return !empty($user['nip']) || !empty($user['name']);
            });

            $incompleteUsers = array_filter($nonAdminUsers, function ($user) {
                return empty($user['nip']) && empty($user['name']);
            });

            // If all have complete data, keep the newest, delete the rest
            if (!empty($completeUsers) && empty($incompleteUsers)) {
                // Sort by created_at desc (newest first)
                usort($completeUsers, function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });

                // Keep first (newest), delete the rest
                for ($i = 1; $i < count($completeUsers); $i++) {
                    $toDelete[] = $completeUsers[$i];
                }
            }
            // If there are incomplete users, delete them and keep complete ones
            elseif (!empty($incompleteUsers)) {
                $toDelete = array_merge($toDelete, $incompleteUsers);
            }
        }

        return $toDelete;
    }

    /**
     * Display summary of duplicates found
     */
    private function displaySummary(array $groups, array $toDelete): void
    {
        $this->newLine();
        $this->info('📊 SUMMARY:');
        $this->info("   - Duplicate groups (nama+NIP): " . count($groups) . " group");
        $this->info("   - Kriteria: Prioritaskan keep yang punya NIP atau nama, skip admin");
        $this->info("   - Total user yang akan dihapus PERMANEN: " . count($toDelete));
        $this->warn("   ⚠️  Penghapusan ini PERMANENT (hard delete), tidak ada undo!");
        $this->newLine();

        $this->displayUsersToDelete($toDelete);
    }

    /**
     * Display users to be deleted
     */
    private function displayUsersToDelete(array $users): void
    {
        if (empty($users)) {
            return;
        }

        $rows = [];
        foreach ($users as $user) {
            $status = $user['is_admin'] ? '✓ Admin' : (is_null($user['deleted_at'] ?? null) ? 'Active' : '🗑️  Deleted');
            $rows[] = [
                $user['id'],
                $user['name'],
                $user['email'] ?? '-',
                $user['nip'] ?? '-',
                $user['unit_kerja_count'] ?? 0,
                $status,
            ];
        }

        $this->table(
            ['ID', 'Nama', 'Email', 'NIP', 'Unit Kerja', 'Status'],
            $rows
        );
    }

    /**
     * Helper to count array elements
     */
    private function count(array $arr): int
    {
        return count($arr);
    }

    /**
     * Display all duplicate groups found
     */
    private function displayAllDuplicateGroups(array $groups): void
    {
        $this->newLine();
        $this->info('🔍 DETAIL - Semua Duplicate Groups:');
        $this->newLine();

        foreach ($groups as $key => $group) {
            $this->info("📌 Group: <fg=yellow>$key</>");

            $rows = [];
            foreach ($group as $user) {
                $status = $user['is_admin'] ? '✓ Admin' : (is_null($user['deleted_at'] ?? null) ? 'Active' : '🗑️  Deleted');
                $rows[] = [
                    $user['id'],
                    $user['name'] ?? '-',
                    $user['email'] ?? '-',
                    $user['nip'] ?? '-',
                    $user['unit_kerja_count'],
                    $status,
                ];
            }

            $this->table(
                ['ID', 'Nama', 'Email', 'NIP', 'Unit Kerja', 'Status'],
                $rows
            );
            $this->newLine();
        }
    }

    /**
     * Delete users permanently (hard delete) including soft-deleted ones
     */
    private function deleteUsers(array $users): int
    {
        $ids = array_column($users, 'id');
        // Use forceDelete to hard delete (permanent deletion)
        // withTrashed() to also delete soft-deleted records
        $count = User::withTrashed()->whereIn('id', $ids)->forceDelete();
        return $count;
    }
}
