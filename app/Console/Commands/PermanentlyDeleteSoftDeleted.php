<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PermanentlyDeleteSoftDeleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:permanent-delete-soft-deleted
                            {--force : Jalankan tanpa konfirmasi}
                            {--dry-run : Preview tanpa benar-benar menghapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus PERMANEN semua user yang sudah soft-deleted (hard delete)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Scanning untuk soft-deleted users...');

        // Get all soft-deleted users
        $softDeletedUsers = User::onlyTrashed()
            ->select('id', 'name', 'email', 'nip', 'deleted_at')
            ->orderBy('deleted_at', 'desc')
            ->get();

        if ($softDeletedUsers->isEmpty()) {
            $this->info('✅ Tidak ada user yang soft-deleted.');
            return 0;
        }

        $count = $softDeletedUsers->count();

        $this->newLine();
        $this->warn("⚠️  DITEMUKAN $count USER YANG SOFT-DELETED");
        $this->warn("   Penghapusan ini PERMANENT (hard delete), tidak ada undo!");
        $this->newLine();

        // Display users to be deleted
        $rows = [];
        foreach ($softDeletedUsers as $user) {
            $rows[] = [
                $user->id,
                $user->name ?? '-',
                $user->email ?? '-',
                $user->nip ?? '-',
                $user->deleted_at,
            ];
        }

        $this->table(
            ['ID', 'Nama', 'Email', 'NIP', 'Deleted At'],
            $rows
        );

        // Ask for confirmation if not dry-run and not force
        if (!$dryRun && !$force) {
            if (!$this->confirm("Apakah Anda yakin untuk hard-delete $count user ini?")) {
                $this->warn('Dibatalkan.');
                return 0;
            }
        }

        // Perform deletion or dry run
        if ($dryRun) {
            $this->info("\n📋 DRY RUN MODE - Data tidak akan dihapus");
            return 0;
        }

        // Hard delete permanently
        $deleted = User::onlyTrashed()->forceDelete();
        $this->info("\n✅ Berhasil menghapus PERMANEN $deleted user.");

        return 0;
    }
}
