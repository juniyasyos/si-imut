<?php

use App\Models\User;
use App\Policies\UnitKerjaPolicy;
use App\Filament\Resources\UnitKerjaResource;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
});

describe('UnitKerjaPolicy', function () {
    it('grants viewAny with permission (from resource)', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(UnitKerjaResource::class);
        $user->givePermissionTo("view_any_{$identifier}");

        $policy = new UnitKerjaPolicy;

        expect($policy->viewAny($user))->toBeTrue();
    });

    it('denies viewAny without permission', function () {
        $user = User::factory()->create();
        $policy = new UnitKerjaPolicy;

        expect($policy->viewAny($user))->toBeFalse();
    });

    it('checks create/update/delete permissions (from resource)', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(UnitKerjaResource::class);
        $user->givePermissionTo([
            "create_{$identifier}",
            "update_{$identifier}",
            "delete_{$identifier}",
        ]);

        $policy = new UnitKerjaPolicy;

        expect($policy->create($user))->toBeTrue();
        expect($policy->update($user))->toBeTrue();
        expect($policy->delete($user))->toBeTrue();
    });

    it('checks bulk and restore/force-delete permissions (from resource)', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(UnitKerjaResource::class);
        $user->givePermissionTo([
            "delete_any_{$identifier}",
            "restore_{$identifier}",
            "restore_any_{$identifier}",
            "force_delete_{$identifier}",
            "force_delete_any_{$identifier}",
        ]);

        $policy = new UnitKerjaPolicy;

        expect($policy->deleteAny($user))->toBeTrue();
        expect($policy->restore($user))->toBeTrue();
        expect($policy->restoreAny($user))->toBeTrue();
        expect($policy->forceDelete($user))->toBeTrue();
        expect($policy->forceDeleteAny($user))->toBeTrue();
    });

    it('checks custom attach permissions (from resource)', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(UnitKerjaResource::class);
        $user->givePermissionTo([
            "attach_user_to_unit_kerja_{$identifier}",
            "attach_imut_data_to_unit_kerja_{$identifier}",
        ]);

        $policy = new UnitKerjaPolicy;

        expect($policy->attachUser($user))->toBeTrue();
        expect($policy->AttachImutData($user))->toBeTrue();
    });

    dataset('unitKerjaAbilities', function () {
        $identifier = FilamentShield::getPermissionIdentifier(UnitKerjaResource::class);
        return [
            'viewAny' => ['viewAny', "view_any_{$identifier}"],
            'create' => ['create', "create_{$identifier}"],
            'update' => ['update', "update_{$identifier}"],
            'delete' => ['delete', "delete_{$identifier}"],
            'deleteAny' => ['deleteAny', "delete_any_{$identifier}"],
            'restore' => ['restore', "restore_{$identifier}"],
            'restoreAny' => ['restoreAny', "restore_any_{$identifier}"],
            'forceDelete' => ['forceDelete', "force_delete_{$identifier}"],
            'forceDeleteAny' => ['forceDeleteAny', "force_delete_any_{$identifier}"],
        ];
    });

    it('allows abilities with matching permissions', function (string $ability, string $perm) {
        $user = User::factory()->create();
        $user->givePermissionTo($perm);
        $policy = new UnitKerjaPolicy;

        expect($policy->{$ability}($user))->toBeTrue();
    })->with('unitKerjaAbilities');

    it('denies abilities when permissions missing', function (string $ability, string $perm) {
        $user = User::factory()->create();
        $policy = new UnitKerjaPolicy;

        expect($policy->{$ability}($user))->toBeFalse();
    })->with('unitKerjaAbilities');
});
