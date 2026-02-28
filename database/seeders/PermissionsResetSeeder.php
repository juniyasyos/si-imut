<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionsResetSeeder extends Seeder
{
    public function run(): void
    {
        // non‑aktifkan cek FK supaya truncate tidak error
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // tabel pivot yang merujuk ke permissions
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_permissions')->truncate();

        // tabel permission sendiri
        Permission::truncate();              // atau ->query()->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // buang cache agar perubahan terlihat segera
        app()
            ->make(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $this->command->info('Hanya permission dan pivot terkait telah dikosongkan.');
    }
}