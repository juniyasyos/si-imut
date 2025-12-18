<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleUpgradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting Role System Upgrade...');

        // Step 1: Delete old/unwanted roles
        $this->cleanupOldRoles();

        // Step 2: Create/Update new role structure
        $this->createNewRoleStructure();

        // Step 3: Migrate existing users to new roles
        $this->migrateExistingUsers();

        $this->command->info('✅ Role System Upgrade Complete!');
    }

    private function cleanupOldRoles(): void
    {
        $this->command->info('🗑️  Cleaning up old roles...');

        $rolesToDelete = [
            'tim_it',
            'IT',
            'unit_kerja',
            'super_admin',
            'Administrator Application'
        ];

        foreach ($rolesToDelete as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $this->command->info("   - Deleting role: {$roleName}");
                // Detach users before deleting role
                $role->users()->detach();
                $role->delete();
            }
        }
    }

    private function createNewRoleStructure(): void
    {
        $this->command->info('🆕 Creating new role structure...');

        $roles = [
            [
                'name' => 'admin',
                'label' => 'Administrator',
                'guard_name' => 'web'
            ],
            [
                'name' => 'tim_mutu',
                'label' => 'Tim Mutu',
                'guard_name' => 'web'
            ],
            [
                'name' => 'pengumpul_data',
                'label' => 'Unit Kerja - Pengumpul Data',
                'guard_name' => 'web'
            ],
            [
                'name' => 'validator_pic',
                'label' => 'Unit Kerja - Validator/PIC Indikator',
                'guard_name' => 'web'
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
            $this->command->info("   ✓ {$roleData['label']}");
        }
    }

    private function migrateExistingUsers(): void
    {
        $this->command->info('👥 Migrating existing users...');

        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Remove all existing roles
            $user->syncRoles([]);

            // Assign default role based on email or other criteria
            if (str_contains($user->email, 'admin') || $user->nip === '001') {
                $user->assignRole('admin');
                $this->command->info("   - {$user->name} → Administrator");
            } elseif (str_contains($user->email, 'mutu')) {
                $user->assignRole('tim_mutu');
                $this->command->info("   - {$user->name} → Tim Mutu");
            } elseif ($user->unitKerjas->isNotEmpty()) {
                // If user has unit kerja, assign pengumpul_data as default
                // You can modify this logic based on your business rules
                $user->assignRole('pengumpul_data');
                $this->command->info("   - {$user->name} → Pengumpul Data");
            } else {
                // Default to pengumpul_data if no specific criteria met
                $user->assignRole('pengumpul_data');
                $this->command->info("   - {$user->name} → Pengumpul Data (default)");
            }
        }

        $this->command->info("   Migrated {$users->count()} users");
    }
}
