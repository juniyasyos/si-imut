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

        // Step 1: Create/Update role structure (no deletion)
        $this->createNewRoleStructure();

        // Step 2: Migrate existing users to new roles
        $this->migrateExistingUsers();

        $this->command->info('✅ Role System Upgrade Complete!');
    }

    private function updateExistingRoles(): void
    {
        $this->command->info('🔄 Updating existing roles...');

        // Update existing role mappings (keep data, just update labels)
        $roleUpdates = [
            'super_admin' => ['name' => 'super_admin', 'label' => 'Administrator'],
            'Administrator Application' => ['name' => 'admin', 'label' => 'Administrator'],
            'unit_kerja' => ['name' => 'pengumpul_data', 'label' => 'Unit Kerja - Pengumpul Data'],
        ];

        foreach ($roleUpdates as $oldName => $newData) {
            $role = Role::where('name', $oldName)->first();
            if ($role) {
                $role->update($newData);
                $this->command->info("   ✓ Updated: {$oldName} → {$newData['label']}");
            }
        }
    }

    private function createNewRoleStructure(): void
    {
        $this->command->info('🆕 Creating/Updating role structure...');

        // First update existing roles
        $this->updateExistingRoles();

        // Then ensure all required roles exist
        $roles = [
            [
                'name' => 'super_admin',
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
            $action = $role->wasRecentlyCreated ? 'Created' : 'Updated';
            $this->command->info("   ✓ {$action}: {$roleData['label']}");
        }
    }

    private function migrateExistingUsers(): void
    {
        $this->command->info('👥 Migrating existing users...');

        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Skip users who already have valid roles
            $currentRoles = $user->roles->pluck('name')->toArray();
            $validRoles = ['admin', 'tim_mutu', 'pengumpul_data', 'validator_pic'];

            if (!empty(array_intersect($currentRoles, $validRoles))) {
                $this->command->info("   - {$user->name} → Already has valid role(s): " . implode(', ', $currentRoles));
                continue;
            }

            // Assign role based on email or other criteria (only if no valid role exists)
            if (str_contains($user->email, 'admin') || $user->nip === '0000.00000') {
                $user->assignRole('admin');
                $this->command->info("   - {$user->name} → Administrator (new assignment)");
            } elseif (str_contains($user->email, 'mutu')) {
                $user->assignRole('tim_mutu');
                $this->command->info("   - {$user->name} → Tim Mutu (new assignment)");
            } elseif ($user->unitKerjas->isNotEmpty()) {
                // If user has unit kerja, assign pengumpul_data as default
                $user->assignRole('pengumpul_data');
                $this->command->info("   - {$user->name} → Pengumpul Data (new assignment)");
            } else {
                // Default to pengumpul_data if no specific criteria met
                $user->assignRole('pengumpul_data');
                $this->command->info("   - {$user->name} → Pengumpul Data (default assignment)");
            }
        }

        $this->command->info("   Migrated {$users->count()} users");
    }
}
