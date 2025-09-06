<?php

use App\Models\User;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Policies\ImutProfilePolicy;
use App\Filament\Resources\ImutProfileResource;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
});

describe('ImutProfilePolicy', function () {
    it('owner can view/update/delete without explicit permissions', function () {
        $owner = User::factory()->create();
        $data = ImutData::factory()->create(['created_by' => $owner->id]);
        $profile = ImutProfile::factory()->create(['imut_data_id' => $data->id]);

        $policy = new ImutProfilePolicy;

        expect($policy->view($owner, $profile))->toBeTrue();
        expect($policy->update($owner, $profile))->toBeTrue();
        expect($policy->delete($owner, $profile))->toBeTrue();
        expect($policy->restore($owner, $profile))->toBeTrue();
        expect($policy->forceDelete($owner, $profile))->toBeTrue();
    });

    it('non-owner cannot view/update/delete without permissions', function () {
        $nonOwner = User::factory()->create();
        $owner = User::factory()->create();
        $data = ImutData::factory()->create(['created_by' => $owner->id]);
        $profile = ImutProfile::factory()->create(['imut_data_id' => $data->id]);

        $policy = new ImutProfilePolicy;

        expect($policy->view($nonOwner, $profile))->toBeFalse();
        expect($policy->update($nonOwner, $profile))->toBeFalse();
        expect($policy->delete($nonOwner, $profile))->toBeFalse();
        expect($policy->restore($nonOwner, $profile))->toBeFalse();
        expect($policy->forceDelete($nonOwner, $profile))->toBeFalse();
    });

    it('viewAny requires permission (from resource)', function () {
        $user = User::factory()->create();
        $policy = new ImutProfilePolicy;
        expect($policy->viewAny($user))->toBeFalse();

        $identifier = FilamentShield::getPermissionIdentifier(ImutProfileResource::class);
        $user->givePermissionTo("view_any_{$identifier}");
        expect($policy->viewAny($user))->toBeTrue();
    });

    it('non-owner can update with force_editable permission', function () {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $data = ImutData::factory()->create(['created_by' => $owner->id]);
        $profile = ImutProfile::factory()->create(['imut_data_id' => $data->id]);

        $policy = new ImutProfilePolicy;

        expect($policy->update($user, $profile))->toBeFalse();
        $identifier = FilamentShield::getPermissionIdentifier(ImutProfileResource::class);
        $user->givePermissionTo("force_editable_{$identifier}");
        expect($policy->update($user, $profile))->toBeTrue();
    });

    dataset('imutProfileAbilitiesModel', function () {
        $identifier = FilamentShield::getPermissionIdentifier(ImutProfileResource::class);
        return [
            'view' => ['view', "view_{$identifier}"],
            'update' => ['update', "update_{$identifier}"],
            'delete' => ['delete', "delete_{$identifier}"],
            'restore' => ['restore', "restore_{$identifier}"],
            'forceDelete' => ['forceDelete', "force_delete_{$identifier}"],
        ];
    });

    it('non-owner allowed per ability when permission granted', function (string $ability, string $perm) {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $data = ImutData::factory()->create(['created_by' => $owner->id]);
        $profile = ImutProfile::factory()->create(['imut_data_id' => $data->id]);

        $user->givePermissionTo($perm);
        $policy = new ImutProfilePolicy;

        expect($policy->{$ability}($user, $profile))->toBeTrue();
    })->with('imutProfileAbilitiesModel');

    dataset('imutProfileAbilitiesBulk', function () {
        $identifier = FilamentShield::getPermissionIdentifier(ImutProfileResource::class);
        return [
            'deleteAny' => ['deleteAny', "delete_any_{$identifier}"],
            'restoreAny' => ['restoreAny', "restore_any_{$identifier}"],
            'forceDeleteAny' => ['forceDeleteAny', "force_delete_any_{$identifier}"],
        ];
    });

    it('bulk abilities require matching permissions', function (string $ability, string $perm) {
        $user = User::factory()->create();
        $policy = new ImutProfilePolicy;
        expect($policy->{$ability}($user))->toBeFalse();

        $user->givePermissionTo($perm);
        expect($policy->{$ability}($user))->toBeTrue();
    })->with('imutProfileAbilitiesBulk');
});
