<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('admin user seeder creates admin user', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', 'admin@redlane.local')->first();

    expect($admin)->not()->toBeNull();
    expect($admin->first_name)->toBe('Admin');
    expect($admin->last_name)->toBe('User');
    expect($admin->email_verified_at)->not()->toBeNull();
});

test('admin user has admin role', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', 'admin@redlane.local')->first();

    expect($admin->hasRole('admin'))->toBeTrue();
});

test('admin user has all permissions', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', 'admin@redlane.local')->first();

    expect($admin->can('manage_duty_categories'))->toBeTrue();
    expect($admin->can('manage_currencies'))->toBeTrue();
    expect($admin->can('manage_users'))->toBeTrue();
    expect($admin->can('view_all_declarations'))->toBeTrue();
    expect($admin->can('manage_feature_flags'))->toBeTrue();
    expect($admin->can('view_audit_logs'))->toBeTrue();
});

test('seeder can be run multiple times without creating duplicate users', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    expect(User::where('email', 'admin@redlane.local')->count())->toBe(1);
});

test('existing admin user keeps admin role when seeder runs again', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    // Create admin user without role
    $admin = User::create([
        'email' => 'admin@redlane.local',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    expect($admin->hasRole('admin'))->toBeFalse();

    // Run seeder
    $this->seed(AdminUserSeeder::class);

    $admin->refresh();
    expect($admin->hasRole('admin'))->toBeTrue();
});
