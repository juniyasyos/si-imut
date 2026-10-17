<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $guard = 'web';
        $permissionName = 'validate_daily::report::entry';

        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => $guard,
        ]);

        // Assign to validator_pic and super_admin roles if they exist
        foreach (['validator_pic', 'super_admin'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role && ! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        // Clear Spatie permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $guard = 'web';
        $permissionName = 'validate_daily::report::entry';

        $permission = Permission::where('name', $permissionName)->where('guard_name', $guard)->first();
        if ($permission) {
            $validatorRole = Role::where('name', 'validator_pic')->where('guard_name', $guard)->first();
            if ($validatorRole) {
                $validatorRole->revokePermissionTo($permission);
            }
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
