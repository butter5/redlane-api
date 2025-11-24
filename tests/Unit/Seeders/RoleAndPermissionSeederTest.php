<?php

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('seeder creates all required roles', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    expect(Role::count())->toBe(3);

    $roles = Role::pluck('name')->toArray();
    expect($roles)->toContain('admin', 'user', 'customs_officer');
});

test('seeder creates all required permissions', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    expect(Permission::count())->toBe(6);

    $permissions = Permission::pluck('name')->toArray();
    expect($permissions)->toContain(
        'manage_duty_categories',
        'manage_currencies',
        'manage_users',
        'view_all_declarations',
        'manage_feature_flags',
        'view_audit_logs'
    );
});

test('admin role has all permissions', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = Role::findByName('admin');
    expect($admin->permissions->count())->toBe(6);

    expect($admin->hasPermissionTo('manage_duty_categories'))->toBeTrue();
    expect($admin->hasPermissionTo('manage_currencies'))->toBeTrue();
    expect($admin->hasPermissionTo('manage_users'))->toBeTrue();
    expect($admin->hasPermissionTo('view_all_declarations'))->toBeTrue();
    expect($admin->hasPermissionTo('manage_feature_flags'))->toBeTrue();
    expect($admin->hasPermissionTo('view_audit_logs'))->toBeTrue();
});

test('user role has no special permissions', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = Role::findByName('user');
    expect($user->permissions->count())->toBe(0);
});

test('customs_officer role has view_all_declarations permission', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $customsOfficer = Role::findByName('customs_officer');
    expect($customsOfficer->permissions->count())->toBe(1);
    expect($customsOfficer->hasPermissionTo('view_all_declarations'))->toBeTrue();
});

test('seeder can be run multiple times without error', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);

    expect(Role::count())->toBe(3);
    expect(Permission::count())->toBe(6);
});
