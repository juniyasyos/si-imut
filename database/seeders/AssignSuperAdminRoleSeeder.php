<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignSuperAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari user admin
        $admin = User::where('nip', '0000.00000')->first();

        if ($admin) {
            // Cari role super_admin
            $superAdminRole = Role::where('name', 'super_admin')->first();

            if ($superAdminRole) {
                // Assign role ke admin
                $admin->assignRole($superAdminRole);
                $this->command->info('Super Admin role assigned to admin user.');
            } else {
                $this->command->error('Super Admin role not found. Please run ShieldSeeder first.');
            }
        } else {
            $this->command->error('Admin user not found.');
        }
    }
}
