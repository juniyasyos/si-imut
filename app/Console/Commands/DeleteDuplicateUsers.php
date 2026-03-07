<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteDuplicateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus user duplicate (nama/nip) tanpa unit kerja - hard delete langsung';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Scanning user duplicate berdasarkan nama/nip tanpa unit kerja...');

        // Find duplicates: group by nama atau nip
        $duplicates = $this->findDuplicates();

        if (empty($duplicates)) {
            $this->info('✅ Tidak ada user duplicate yang perlu dihapus.');
            return 0;
        }

        $count = count($duplicates);
        $this->newLine();
        $this->warn("⚠️  Ditemukan $count user yang akan dihapus PERMANEN (hard delete, tidak bisa di-undo!)");
        $this->newLine();

        // Display users to delete
        $this->displayUsers($duplicates);

        // Confirm before delete
        if (!$this->confirm('Lanjutkan hapus?')) {
            $this->warn('Dibatalkan.');
            return 0;
        }

        // Hard delete
        $ids = array_column($duplicates, 'id');
        $deleted = User::withTrashed()->whereIn('id', $ids)->forceDelete();

        $this->newLine();
        $this->info("✅ Berhasil menghapus PERMANEN $deleted user.");

        return 0;
    }

    /**
     * Find duplicate users by nama OR nip, then filter only those without unit kerja
     * Keep strategy: prioritize users WITH unit kerja, delete extra copies
     */
    private function findDuplicates(): array
    {
        // Get all users (including soft-deleted)
        $allUsers = User::withTrashed()
            ->with('unitKerjas')
            ->select('id', 'name', 'email', 'nip', 'deleted_at')
            ->orderBy('id', 'asc') // Keep older users, delete newer duplicates
            ->get();

        if ($allUsers->isEmpty()) {
            return [];
        }

        // Group by name or nip
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
                'email' => $user->email,
                'nip' => $user->nip,
                'unit_kerja_count' => $user->unitKerjas->count(),
            ];
        }

        // Find users to delete: keep strategy
        $toDelete = [];

        foreach ($groups as $group) {
            if (count($group) > 1) {
                // This group has duplicates
                // Strategy: keep 1, delete rest (prefer keeping those with unit kerja)

                $withUnitKerja = array_filter($group, fn($u) => $u['unit_kerja_count'] > 0);
                $withoutUnitKerja = array_filter($group, fn($u) => $u['unit_kerja_count'] == 0);

                // Keep the first one WITH unit kerja if exists, otherwise keep first one
                $keepId = null;

                if (!empty($withUnitKerja)) {
                    // Keep user with unit kerja
                    $keepUser = reset($withUnitKerja);
                    $keepId = $keepUser['id'];
                } else {
                    // Keep the oldest (first) one
                    $keepUser = reset($group);
                    $keepId = $keepUser['id'];
                }

                // Delete all others that don't have unit kerja
                foreach ($group as $user) {
                    if ($user['id'] !== $keepId && $user['unit_kerja_count'] == 0) {
                        $toDelete[] = $user;
                    }
                }
            }
        }

        return $toDelete;
    }

    /**
     * Display users to delete
     */
    private function displayUsers(array $users): void
    {
        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                $user['id'],
                $user['name'] ?? '-',
                $user['email'] ?? '-',
                $user['nip'] ?? '-',
            ];
        }

        $this->table(['ID', 'Nama', 'Email', 'NIP'], $rows);
    }
}
