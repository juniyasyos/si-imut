<?php

use App\Models\User;
use App\Models\ImutCategory;
use App\Policies\ImutCategoryPolicy;
use App\Filament\Resources\ImutCategoryResource;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
});

describe('ImutCategoryPolicy', function () {
    it('grants viewAny and view with permissions (from resource)', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(ImutCategoryResource::class);
        $user->givePermissionTo([
            "view_any_{$identifier}",
            "view_{$identifier}",
        ]);

        $policy = new ImutCategoryPolicy;
        $category = ImutCategory::factory()->create();

        expect($policy->viewAny($user))->toBeTrue();
        expect($policy->view($user, $category))->toBeTrue();
    });

    it('checks create/update/delete permissions (from resource)', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(ImutCategoryResource::class);
        $user->givePermissionTo([
            "create_{$identifier}",
            "update_{$identifier}",
            "delete_{$identifier}",
        ]);

        $policy = new ImutCategoryPolicy;
        $category = ImutCategory::factory()->create();

        expect($policy->create($user))->toBeTrue();
        expect($policy->update($user, $category))->toBeTrue();
        expect($policy->delete($user, $category))->toBeTrue();
    });

    it('checks bulk/restore/force-delete permissions (from resource)', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(ImutCategoryResource::class);
        $user->givePermissionTo([
            "delete_any_{$identifier}",
            "restore_{$identifier}",
            "restore_any_{$identifier}",
            "force_delete_{$identifier}",
            "force_delete_any_{$identifier}",
        ]);

        $policy = new ImutCategoryPolicy;
        $category = ImutCategory::factory()->make();

        expect($policy->deleteAny($user))->toBeTrue();
        expect($policy->restore($user, $category))->toBeTrue();
        expect($policy->restoreAny($user))->toBeTrue();
        expect($policy->forceDelete($user, $category))->toBeTrue();
        expect($policy->forceDeleteAny($user))->toBeTrue();
    });

    it('denies all abilities without permissions', function () {
        $user = User::factory()->create();
        $policy = new ImutCategoryPolicy;
        $category = ImutCategory::factory()->make();

        expect($policy->viewAny($user))->toBeFalse();
        expect($policy->view($user, $category))->toBeFalse();
        expect($policy->create($user))->toBeFalse();
        expect($policy->update($user, $category))->toBeFalse();
        expect($policy->delete($user, $category))->toBeFalse();
        expect($policy->deleteAny($user))->toBeFalse();
        expect($policy->forceDelete($user, $category))->toBeFalse();
        expect($policy->forceDeleteAny($user))->toBeFalse();
        expect($policy->restore($user, $category))->toBeFalse();
        expect($policy->restoreAny($user))->toBeFalse();
    });

    dataset('imutCategoryAbilities', function () {
        $identifier = FilamentShield::getPermissionIdentifier(ImutCategoryResource::class);
        return [
            'viewAny' => ['viewAny', "view_any_{$identifier}", false],
            'view' => ['view', "view_{$identifier}", true],
            'create' => ['create', "create_{$identifier}", false],
            'update' => ['update', "update_{$identifier}", true],
            'delete' => ['delete', "delete_{$identifier}", true],
            'deleteAny' => ['deleteAny', "delete_any_{$identifier}", false],
            'forceDelete' => ['forceDelete', "force_delete_{$identifier}", true],
            'forceDeleteAny' => ['forceDeleteAny', "force_delete_any_{$identifier}", false],
            'restore' => ['restore', "restore_{$identifier}", true],
            'restoreAny' => ['restoreAny', "restore_any_{$identifier}", false],
        ];
    });

    it('allows specific ability when corresponding permission granted', function (string $ability, string $permission, bool $needsModel) {
        $user = User::factory()->create();
        $user->givePermissionTo($permission);
        $policy = new ImutCategoryPolicy;
        $category = ImutCategory::factory()->make();

        $result = $needsModel
            ? $policy->{$ability}($user, $category)
            : $policy->{$ability}($user);

        expect($result)->toBeTrue();
    })->with('imutCategoryAbilities');

    it('denies specific ability when permission missing', function (string $ability, string $permission, bool $needsModel) {
        $user = User::factory()->create();
        $policy = new ImutCategoryPolicy;
        $category = ImutCategory::factory()->make();

        $result = $needsModel
            ? $policy->{$ability}($user, $category)
            : $policy->{$ability}($user);

        expect($result)->toBeFalse();
    })->with('imutCategoryAbilities');

    it('role-based permissions aggregate and can be updated dynamically', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(ImutCategoryResource::class);
        $role = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Manajer Mutu']);
        $role->syncPermissions(["view_any_{$identifier}", "view_{$identifier}", "create_{$identifier}"]);
        $user->assignRole($role);

        $policy = new ImutCategoryPolicy;
        $category = ImutCategory::factory()->make();

        expect($policy->viewAny($user))->toBeTrue();
        expect($policy->view($user, $category))->toBeTrue();
        expect($policy->create($user))->toBeTrue();

        // Initially missing update permission
        expect($policy->update($user, $category))->toBeFalse();

        // Grant new permission via role
        $role->givePermissionTo("update_{$identifier}");
        $user->refresh();
        expect($policy->update($user, $category))->toBeTrue();

        // Revoke and ensure denial
        $role->revokePermissionTo("update_{$identifier}");
        $user->refresh();
        expect($policy->update($user, $category))->toBeFalse();
    });

    it('merges permissions from multiple roles', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(ImutCategoryResource::class);
        $roleA = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'A']);
        $roleB = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'B']);
        $roleA->syncPermissions(["update_{$identifier}"]);
        $roleB->syncPermissions(["delete_{$identifier}"]);
        $user->syncRoles([$roleA, $roleB]);

        $policy = new ImutCategoryPolicy;
        $category = ImutCategory::factory()->make();

        expect($policy->update($user, $category))->toBeTrue();
        expect($policy->delete($user, $category))->toBeTrue();
    });
});
