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

        // Ensure required roles exist to avoid RoleDoesNotExist errors
        $requiredRoles = ['super_admin', 'tim_mutu', 'pengumpul_data', 'validator_pic'];
        foreach ($requiredRoles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Step: Migrate existing users to new roles
        $this->migrateExistingUsers();

        $this->command->info('✅ Role System Upgrade Complete!');
    }

    private function migrateExistingUsers(): void
    {
        $this->command->info('👥 Migrating existing users...');

        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Skip users who already have valid roles
            $currentRoles = $user->roles->pluck('name')->toArray();
            $validRoles = ['super_admin', 'tim_mutu', 'pengumpul_data', 'validator_pic'];

            if (!empty(array_intersect($currentRoles, $validRoles))) {
                $this->command->info("   - {$user->name} → Already has valid role(s): " . implode(', ', $currentRoles));
                continue;
            }

            // Assign role based on email or other criteria (only if no valid role exists)
            if ($user->nip === '0000.00000') {
                $user->assignRole('super_admin');
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

        $this->command->info("Migrated {$users->count()} users");
    }
}
