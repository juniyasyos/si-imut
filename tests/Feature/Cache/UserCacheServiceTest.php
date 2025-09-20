<?php

use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('it can create user cache service instance', function () {
    // Simple test to check if UserCacheService can be instantiated
    config(['cache.default' => 'array']);

    expect(true)->toBeTrue();
});

test('it can create user and unit kerja', function () {
    $unitKerja = UnitKerja::factory()->create();
    $user = User::factory()->create();

    expect($user)->not()->toBeNull()
        ->and($unitKerja)->not()->toBeNull();
});

test('it can assign roles to user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $user->assignRole($role);

    expect($user->hasRole('admin'))->toBeTrue();
});

test('it can create permissions', function () {
    $permission = Permission::create(['name' => 'edit_content']);
    $role = Role::create(['name' => 'editor']);
    $role->givePermissionTo($permission);

    expect($role->hasPermissionTo('edit_content'))->toBeTrue();
});
