<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsResetSeeder extends Seeder
{
    public function run(): void
    {
        // matikan cek FK agar truncate tidak error
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();

        Permission::truncate();
        Role::truncate();

        // hidupkan kembali cek FK
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // kosongkan cache permission
        app()
            ->make(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $this->command->info('Role & permission tables truncated.');
    }
}
