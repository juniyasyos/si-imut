<?php

use App\Models\User;
use App\Policies\ImutDataPolicy;
use App\Filament\Resources\ImutDataResource;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
});

describe('ImutDataPolicy', function () {
    dataset('imutDataAbilities', function () {
        $identifier = FilamentShield::getPermissionIdentifier(ImutDataResource::class);
        return [
            'viewAny' => ['viewAny', "view_any_{$identifier}"],
            'viewAll' => ['viewAll', "view_all_data_{$identifier}"],
            'viewByUnitKerja' => ['viewByUnitKerja', "view_by_unit_kerja_{$identifier}"],
            'create' => ['create', "create_{$identifier}"],
            'update' => ['update', "update_{$identifier}"],
            'deleteAny' => ['deleteAny', "delete_any_{$identifier}"],
            'restoreAny' => ['restoreAny', "restore_any_{$identifier}"],
            'forceDeleteAny' => ['forceDeleteAny', "force_delete_any_{$identifier}"],
        ];
    });

    it('allows ability when permission granted', function (string $ability, string $perm) {
        $user = User::factory()->create();
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        $user->givePermissionTo($perm);
        $policy = new ImutDataPolicy;

        expect($policy->{$ability}($user))->toBeTrue();
    })->with('imutDataAbilities');

    it('denies ability when permission missing', function (string $ability, string $perm) {
        $user = User::factory()->create();
        $policy = new ImutDataPolicy;

        expect($policy->{$ability}($user))->toBeFalse();
    })->with('imutDataAbilities');

    it('allows model-specific delete/restore/forceDelete with permissions (from resource)', function () {
        $user = User::factory()->create();
        $identifier = FilamentShield::getPermissionIdentifier(ImutDataResource::class);
        foreach (["delete_{$identifier}", "restore_{$identifier}", "force_delete_{$identifier}"] as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $user->givePermissionTo(["delete_{$identifier}", "restore_{$identifier}", "force_delete_{$identifier}"]);
        $policy = new ImutDataPolicy;

        // These methods in policy accept a model instance; use a dummy via factory->make()
        $model = App\Models\ImutData::factory()->make();

        expect($policy->delete($user, $model))->toBeTrue();
        expect($policy->restore($user, $model))->toBeTrue();
        expect($policy->forceDelete($user, $model))->toBeTrue();
    });
});
