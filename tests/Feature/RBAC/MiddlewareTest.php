<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin middleware allows admin users', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/admin/test-admin-access');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Admin access granted',
        ]);
});

test('admin middleware blocks non-admin users', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/admin/test-admin-access');

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Unauthorized. Admin access required.',
        ]);
});

test('admin middleware blocks users without role', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/admin/test-admin-access');

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Unauthorized. Admin access required.',
        ]);
});

test('admin middleware blocks customs officers', function () {
    $customsOfficer = User::factory()->create(['email_verified_at' => now()]);
    $customsOfficer->assignRole('customs_officer');

    Sanctum::actingAs($customsOfficer);

    $response = $this->getJson('/api/v1/admin/test-admin-access');

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Unauthorized. Admin access required.',
        ]);
});

test('admin middleware requires authentication', function () {
    $response = $this->getJson('/api/v1/admin/test-admin-access');

    $response->assertStatus(401);
});

test('permission middleware allows users with permission', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->givePermissionTo('manage_duty_categories');

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/test-permission-access');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Permission granted',
        ]);
});

test('permission middleware blocks users without permission', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/test-permission-access');

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Unauthorized. Required permission: manage_duty_categories',
        ]);
});

test('permission middleware allows admin users', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/test-permission-access');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Permission granted',
        ]);
});

test('permission middleware requires authentication', function () {
    $response = $this->getJson('/api/v1/test-permission-access');

    $response->assertStatus(401);
});
