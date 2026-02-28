<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsResetSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     *
     * Tabel-tabel yang dikosongkan:
     *   - role_has_permissions
     *   - model_has_permissions
     *   - model_has_roles
     *   - permissions
     *   - roles
     *
     * Selebihnya tidak disentuh.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('role_has_permissions')->truncate();
            DB::table('model_has_permissions')->truncate();
            DB::table('model_has_roles')->truncate();

            Permission::truncate();
            Role::truncate();
        });

        // supaya cache permission tidak lagi menyimpan data lama
        app()
            ->make(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $this->command->info('Role & permission tables truncated.');
    }
}
