<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('user can be assigned a role', function () {
    $user = User::factory()->create();

    $user->assignRole('user');

    expect($user->hasRole('user'))->toBeTrue();
    expect($user->roles->count())->toBe(1);
});

test('user can be assigned multiple roles', function () {
    $user = User::factory()->create();

    $user->assignRole(['user', 'admin']);

    expect($user->hasRole('user'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->roles->count())->toBe(2);
});

test('admin user has all permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    expect($user->can('manage_duty_categories'))->toBeTrue();
    expect($user->can('manage_currencies'))->toBeTrue();
    expect($user->can('manage_users'))->toBeTrue();
    expect($user->can('view_all_declarations'))->toBeTrue();
    expect($user->can('manage_feature_flags'))->toBeTrue();
    expect($user->can('view_audit_logs'))->toBeTrue();
});

test('regular user has no special permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    expect($user->can('manage_duty_categories'))->toBeFalse();
    expect($user->can('manage_currencies'))->toBeFalse();
    expect($user->can('manage_users'))->toBeFalse();
    expect($user->can('view_all_declarations'))->toBeFalse();
    expect($user->can('manage_feature_flags'))->toBeFalse();
    expect($user->can('view_audit_logs'))->toBeFalse();
});

test('customs_officer user has view_all_declarations permission', function () {
    $user = User::factory()->create();
    $user->assignRole('customs_officer');

    expect($user->can('view_all_declarations'))->toBeTrue();
    expect($user->can('manage_duty_categories'))->toBeFalse();
    expect($user->can('manage_users'))->toBeFalse();
});

test('user without role has no permissions', function () {
    $user = User::factory()->create();

    expect($user->roles->count())->toBe(0);
    expect($user->can('manage_users'))->toBeFalse();
    expect($user->can('view_all_declarations'))->toBeFalse();
});

test('user can be given direct permission without role', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('manage_duty_categories');

    expect($user->can('manage_duty_categories'))->toBeTrue();
    expect($user->can('manage_users'))->toBeFalse();
});

test('role can be removed from user', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    expect($user->hasRole('admin'))->toBeTrue();

    $user->removeRole('admin');

    expect($user->hasRole('admin'))->toBeFalse();
});
