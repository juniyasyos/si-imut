<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleUpgradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting Role System Upgrade...');

        // Cek role yang ada di database
        $existingRoles = Role::pluck('name')->toArray();
        $this->command->info('Existing roles in database: ' . implode(', ', $existingRoles));

        // Jika ada 'super_admin', 'tim_mutu', dan 'unit_kerja', rename 'unit_kerja' menjadi 'pengumpul_data'
        if (in_array('super_admin', $existingRoles) && in_array('tim_mutu', $existingRoles) && in_array('unit_kerja', $existingRoles)) {
            $unitKerjaRole = Role::where('name', 'unit_kerja')->first();
            if ($unitKerjaRole) {
                $unitKerjaRole->update(['name' => 'pengumpul_data']);
                $this->command->info('✅ Renamed "unit_kerja" to "pengumpul_data"');
            }
        }

        // Load roles with permissions from PHP files
        $path = database_path('data/shield_roles');
        $roleFiles = glob("$path/*.php");
        $rolesWithPermissions = collect($roleFiles)
            ->map(fn($file) => require $file)
            ->toArray();

        // Ensure required roles exist and assign permissions
        $requiredRoles = ['super_admin', 'tim_mutu', 'pengumpul_data', 'validator_pic'];
        foreach ($requiredRoles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            // Assign permissions if defined in loaded roles
            $roleData = collect($rolesWithPermissions)->firstWhere('name', $roleName);
            if ($roleData && !empty($roleData['permissions'])) {
                $permissions = collect($roleData['permissions'])->map(
                    fn($perm) => Permission::firstOrCreate([
                        'name' => $perm,
                        'guard_name' => 'web',
                    ])
                );
                $role->permissions()->syncWithoutDetaching($permissions);
            }
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
