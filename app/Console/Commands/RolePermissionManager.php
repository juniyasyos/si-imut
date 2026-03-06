<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionManager extends Command
{
    protected $signature = 'role:permission
                            {action : Aksi yang dilakukan: list-roles | check | set | revoke | sync | list-permissions}
                            {--role= : Nama role yang ingin di-cek atau dimodifikasi}
                            {--permission= : Nama permission (bisa multiple, pisahkan dengan koma)}
                            {--guard=web : Guard name (default: web)}
                            {--filter= : Filter permission berdasarkan kata kunci}';

    protected $description = 'Manajemen permission role: cek, set, revoke, atau sync permission ke/dari role';

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list-roles'       => $this->listRoles(),
            'list-permissions' => $this->listPermissions(),
            'check'            => $this->checkRolePermissions(),
            'set'              => $this->setPermission(),
            'revoke'           => $this->revokePermission(),
            'sync'             => $this->syncPermissions(),
            default            => $this->unknownAction($action),
        };
    }

    // ─── list-roles ───────────────────────────────────────────────────────────

    private function listRoles(): int
    {
        $guard  = $this->option('guard');
        $filter = $this->option('filter');

        $query = Role::with('permissions')->where('guard_name', $guard);

        if ($filter) {
            $query->where('name', 'like', "%{$filter}%");
        }

        $roles = $query->orderBy('name')->get();

        if ($roles->isEmpty()) {
            $this->warn("Tidak ada role ditemukan (guard: {$guard}).");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->line("<fg=cyan;options=bold>=== Daftar Role di Sistem (guard: {$guard}) ===</>");
        $this->newLine();

        $rows = $roles->map(fn($role) => [
            $role->id,
            $role->name,
            $role->permissions->count(),
            $role->users()->count(),
        ]);

        $this->table(
            ['ID', 'Nama Role', 'Jumlah Permission', 'Jumlah User'],
            $rows
        );

        $this->newLine();
        $this->line("<fg=green>Total: {$roles->count()} role</>");
        $this->newLine();

        return self::SUCCESS;
    }

    // ─── list-permissions ─────────────────────────────────────────────────────

    private function listPermissions(): int
    {
        $guard  = $this->option('guard');
        $filter = $this->option('filter');

        $query = Permission::where('guard_name', $guard);

        if ($filter) {
            $query->where('name', 'like', "%{$filter}%");
        }

        $permissions = $query->orderBy('name')->get();

        if ($permissions->isEmpty()) {
            $this->warn("Tidak ada permission ditemukan (guard: {$guard}" . ($filter ? ", filter: {$filter}" : '') . ').');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->line("<fg=cyan;options=bold>=== Daftar Permission di Sistem" . ($filter ? " (filter: {$filter})" : '') . " ===</>");
        $this->newLine();

        // Group berdasarkan prefix resource
        $grouped = $permissions->groupBy(function ($perm) {
            $parts = explode('_', $perm->name);
            // Ambil suffix sebagai group (misal: "user", "laporan::imut")
            return collect($parts)->last() ?? 'other';
        })->sortKeys();

        foreach ($grouped as $group => $perms) {
            $this->line("<fg=yellow>[ {$group} ]</>");
            foreach ($perms as $perm) {
                $this->line("  - {$perm->name}");
            }
            $this->newLine();
        }

        $this->line("<fg=green>Total: {$permissions->count()} permission</>");
        $this->newLine();

        return self::SUCCESS;
    }

    // ─── check ────────────────────────────────────────────────────────────────

    private function checkRolePermissions(): int
    {
        $roleName = $this->getRoleName();
        if (! $roleName) return self::FAILURE;

        $guard  = $this->option('guard');
        $filter = $this->option('filter');

        $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();

        if (! $role) {
            $this->error("Role '{$roleName}' tidak ditemukan (guard: {$guard}).");
            $this->suggestRoles($guard);
            return self::FAILURE;
        }

        $permissions = $role->permissions()
            ->when($filter, fn($q) => $q->where('name', 'like', "%{$filter}%"))
            ->orderBy('name')
            ->get();

        $this->newLine();
        $this->line("<fg=cyan;options=bold>=== Permission Role: {$role->name} (ID: {$role->id}) ===</>");
        $this->newLine();

        if ($permissions->isEmpty()) {
            $this->warn("Role ini tidak memiliki permission" . ($filter ? " yang cocok dengan filter '{$filter}'" : '') . '.');
            $this->newLine();
            return self::SUCCESS;
        }

        // Jika ada filter permission tertentu via --permission
        $targetPermission = $this->option('permission');
        if ($targetPermission) {
            $targets = array_map('trim', explode(',', $targetPermission));

            $this->line('<fg=yellow>Cek permission spesifik:</>');
            $this->newLine();

            $rows = [];
            foreach ($targets as $target) {
                $has    = $role->permissions->where('name', $target)->isNotEmpty();
                $exists = Permission::where('name', $target)->where('guard_name', $guard)->exists();
                $rows[] = [
                    $target,
                    $exists ? '<fg=green>✓ Ada</>' : '<fg=red>✗ Tidak terdaftar</>',
                    $has ? '<fg=green>✓ Ya</>' : '<fg=red>✗ Tidak</>',
                ];
            }

            $this->table(['Permission', 'Terdaftar di Sistem', 'Dimiliki Role'], $rows);
            $this->newLine();
            return self::SUCCESS;
        }

        // Tampilkan semua permission role
        $this->line("<fg=white>Total: {$permissions->count()} permission</>");
        $this->newLine();

        $rows = $permissions->map(fn($p, $i) => [
            $i + 1,
            $p->name,
        ]);

        $this->table(['#', 'Nama Permission'], $rows);
        $this->newLine();

        return self::SUCCESS;
    }

    // ─── set ──────────────────────────────────────────────────────────────────

    private function setPermission(): int
    {
        $roleName        = $this->getRoleName();
        $permissionNames = $this->getPermissionNames();

        if (! $roleName || ! $permissionNames) return self::FAILURE;

        $guard = $this->option('guard');
        $role  = Role::where('name', $roleName)->where('guard_name', $guard)->first();

        if (! $role) {
            $this->error("Role '{$roleName}' tidak ditemukan (guard: {$guard}).");
            $this->suggestRoles($guard);
            return self::FAILURE;
        }

        $this->newLine();
        $this->line("<fg=cyan;options=bold>=== Set Permission ke Role: {$role->name} ===</>");
        $this->newLine();

        $added    = [];
        $skipped  = [];
        $notFound = [];

        foreach ($permissionNames as $permName) {
            $permission = Permission::where('name', $permName)
                ->where('guard_name', $guard)
                ->first();

            if (! $permission) {
                $notFound[] = $permName;
                continue;
            }

            if ($role->hasPermissionTo($permission)) {
                $skipped[] = $permName;
                continue;
            }

            $role->givePermissionTo($permission);
            $added[] = $permName;
        }

        if ($added) {
            $this->line('<fg=green>✓ Permission berhasil ditambahkan:</>');
            foreach ($added as $p) $this->line("  + {$p}");
            $this->newLine();
        }

        if ($skipped) {
            $this->line('<fg=yellow>⚠ Permission sudah dimiliki (dilewati):</>');
            foreach ($skipped as $p) $this->line("  = {$p}");
            $this->newLine();
        }

        if ($notFound) {
            $this->line('<fg=red>✗ Permission tidak ditemukan di sistem:</>');
            foreach ($notFound as $p) $this->line("  ? {$p}");
            $this->newLine();
        }

        $this->clearCache();
        return self::SUCCESS;
    }

    // ─── revoke ───────────────────────────────────────────────────────────────

    private function revokePermission(): int
    {
        $roleName        = $this->getRoleName();
        $permissionNames = $this->getPermissionNames();

        if (! $roleName || ! $permissionNames) return self::FAILURE;

        $guard = $this->option('guard');
        $role  = Role::where('name', $roleName)->where('guard_name', $guard)->first();

        if (! $role) {
            $this->error("Role '{$roleName}' tidak ditemukan (guard: {$guard}).");
            $this->suggestRoles($guard);
            return self::FAILURE;
        }

        $this->newLine();
        $this->line("<fg=cyan;options=bold>=== Revoke Permission dari Role: {$role->name} ===</>");
        $this->newLine();

        $revoked  = [];
        $skipped  = [];
        $notFound = [];

        foreach ($permissionNames as $permName) {
            $permission = Permission::where('name', $permName)
                ->where('guard_name', $guard)
                ->first();

            if (! $permission) {
                $notFound[] = $permName;
                continue;
            }

            if (! $role->hasPermissionTo($permission)) {
                $skipped[] = $permName;
                continue;
            }

            $role->revokePermissionTo($permission);
            $revoked[] = $permName;
        }

        if ($revoked) {
            $this->line('<fg=green>✓ Permission berhasil di-revoke:</>');
            foreach ($revoked as $p) $this->line("  - {$p}");
            $this->newLine();
        }

        if ($skipped) {
            $this->line('<fg=yellow>⚠ Permission tidak dimiliki role (dilewati):</>');
            foreach ($skipped as $p) $this->line("  = {$p}");
            $this->newLine();
        }

        if ($notFound) {
            $this->line('<fg=red>✗ Permission tidak ditemukan di sistem:</>');
            foreach ($notFound as $p) $this->line("  ? {$p}");
            $this->newLine();
        }

        $this->clearCache();
        return self::SUCCESS;
    }

    // ─── sync ─────────────────────────────────────────────────────────────────

    private function syncPermissions(): int
    {
        $roleName        = $this->getRoleName();
        $permissionNames = $this->getPermissionNames();

        if (! $roleName || ! $permissionNames) return self::FAILURE;

        $guard = $this->option('guard');
        $role  = Role::where('name', $roleName)->where('guard_name', $guard)->first();

        if (! $role) {
            $this->error("Role '{$roleName}' tidak ditemukan (guard: {$guard}).");
            $this->suggestRoles($guard);
            return self::FAILURE;
        }

        // Validasi semua permission ada
        $validPermissions = [];
        $notFound         = [];

        foreach ($permissionNames as $permName) {
            $perm = Permission::where('name', $permName)->where('guard_name', $guard)->first();
            if ($perm) {
                $validPermissions[] = $perm;
            } else {
                $notFound[] = $permName;
            }
        }

        if ($notFound) {
            $this->line('<fg=red>✗ Permission tidak ditemukan (tidak disertakan dalam sync):</>');
            foreach ($notFound as $p) $this->line("  ? {$p}");
            $this->newLine();
        }

        $oldPermissions = $role->permissions->pluck('name')->sort()->values();
        $newPermissions = collect($validPermissions)->pluck('name')->sort()->values();

        $willAdd    = $newPermissions->diff($oldPermissions);
        $willRevoke = $oldPermissions->diff($newPermissions);

        $this->newLine();
        $this->line("<fg=cyan;options=bold>=== Sync Permission ke Role: {$role->name} ===</>");
        $this->newLine();
        $this->line("Sebelum : {$oldPermissions->count()} permission");
        $this->line("Sesudah : {$newPermissions->count()} permission");
        $this->newLine();

        if ($willAdd->isNotEmpty()) {
            $this->line('<fg=green>Permission yang akan DITAMBAH:</>');
            foreach ($willAdd as $p) $this->line("  + {$p}");
            $this->newLine();
        }

        if ($willRevoke->isNotEmpty()) {
            $this->line('<fg=red>Permission yang akan DIHAPUS:</>');
            foreach ($willRevoke as $p) $this->line("  - {$p}");
            $this->newLine();
        }

        if ($willAdd->isEmpty() && $willRevoke->isEmpty()) {
            $this->info('Tidak ada perubahan — permission sudah sama.');
            $this->newLine();
            return self::SUCCESS;
        }

        if (! $this->confirm('Lanjutkan sync?', true)) {
            $this->warn('Dibatalkan.');
            return self::SUCCESS;
        }

        $role->syncPermissions($validPermissions);
        $this->clearCache();

        $this->info("✓ Sync berhasil! Role '{$role->name}' sekarang memiliki {$newPermissions->count()} permission.");
        $this->newLine();

        return self::SUCCESS;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getRoleName(): ?string
    {
        $role = $this->option('role');

        if (! $role) {
            $this->error('Opsi --role wajib diisi.');
            $this->line('Contoh: php artisan role:permission check --role=tim_mutu');
            $this->newLine();
        }

        return $role;
    }

    private function getPermissionNames(): ?array
    {
        $permission = $this->option('permission');

        if (! $permission) {
            $this->error('Opsi --permission wajib diisi untuk aksi ini.');
            $this->line('Contoh: php artisan role:permission set --role=tim_mutu --permission=impersonate_user,view_user');
            $this->newLine();
            return null;
        }

        return array_map('trim', explode(',', $permission));
    }

    private function suggestRoles(string $guard): void
    {
        $roles = Role::where('guard_name', $guard)->orderBy('name')->pluck('name');
        if ($roles->isEmpty()) return;

        $this->newLine();
        $this->line('<fg=yellow>Role yang tersedia:</>');
        foreach ($roles as $r) {
            $this->line("  - {$r}");
        }
    }

    private function clearCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->line('<fg=gray>✓ Cache permission di-clear.</>');
    }

    private function unknownAction(string $action): int
    {
        $this->error("Aksi '{$action}' tidak dikenal.");
        $this->newLine();
        $this->line('<fg=yellow>Aksi yang tersedia:</>');
        $this->table(
            ['Aksi', 'Deskripsi', 'Opsi Wajib'],
            [
                ['list-roles',       'Tampilkan semua role di sistem',                    '-'],
                ['list-permissions', 'Tampilkan semua permission di sistem',              '-'],
                ['check',            'Cek daftar permission milik sebuah role',           '--role'],
                ['set',              'Tambah permission ke role',                         '--role, --permission'],
                ['revoke',           'Hapus permission dari role',                        '--role, --permission'],
                ['sync',             'Sinkronkan permission role (replace semua)',        '--role, --permission'],
            ]
        );
        $this->newLine();
        $this->line('<fg=cyan>Contoh penggunaan:</>');
        $this->line('  php artisan role:permission list-roles');
        $this->line('  php artisan role:permission list-permissions --filter=user');
        $this->line('  php artisan role:permission check --role=tim_mutu');
        $this->line('  php artisan role:permission check --role=tim_mutu --permission=impersonate_user,view_user');
        $this->line('  php artisan role:permission set --role=tim_mutu --permission=impersonate_user');
        $this->line('  php artisan role:permission revoke --role=tim_mutu --permission=impersonate_user');
        $this->line('  php artisan role:permission sync --role=tim_mutu --permission=view_user,export_user');
        $this->newLine();

        return self::FAILURE;
    }
}
