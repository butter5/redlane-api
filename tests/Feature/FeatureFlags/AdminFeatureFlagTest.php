<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('admin can get all feature flags with statistics', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/admin/feature-flags');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'flags' => [
                    'ocr_processing' => ['global', 'user_overrides'],
                    'multi_leg_trips' => ['global', 'user_overrides'],
                    'admin_dashboard' => ['global', 'user_overrides'],
                    'declaration_export' => ['global', 'user_overrides'],
                    'currency_api_integration' => ['global', 'user_overrides'],
                ],
            ],
        ]);
});

test('unauthenticated user cannot access admin endpoints', function () {
    $response = $this->getJson('/api/v1/admin/feature-flags');

    $response->assertStatus(401);
});

test('admin can toggle feature flag globally', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($admin);

    // Get initial state
    $service = app(\App\Services\FeatureFlagService::class);
    $initialState = $service->isActive('ocr_processing', null);

    // Toggle the flag
    $response = $this->postJson('/api/v1/admin/feature-flags/ocr_processing/toggle');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'flag' => 'ocr_processing',
                'enabled' => ! $initialState,
            ],
            'message' => 'Feature flag toggled successfully',
        ]);

    // Verify the state changed
    expect($service->isActive('ocr_processing', null))->toBe(! $initialState);
});

test('admin cannot toggle invalid feature flag', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/feature-flags/invalid_flag/toggle');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Feature flag not found',
        ]);
});

test('admin can enable feature flag for specific user', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $targetUser = User::factory()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/v1/admin/feature-flags/ocr_processing/users/{$targetUser->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'flag' => 'ocr_processing',
                'user_id' => $targetUser->id,
                'enabled' => true,
            ],
            'message' => 'Feature flag enabled for user',
        ]);

    // Verify the flag is enabled for the user
    $service = app(\App\Services\FeatureFlagService::class);
    expect($service->isActive('ocr_processing', $targetUser))->toBe(true);
});

test('admin can disable feature flag for specific user', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $targetUser = User::factory()->create();
    Sanctum::actingAs($admin);

    // First enable it
    $service = app(\App\Services\FeatureFlagService::class);
    $service->enableForUser('ocr_processing', $targetUser);

    // Then disable it
    $response = $this->deleteJson("/api/v1/admin/feature-flags/ocr_processing/users/{$targetUser->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'flag' => 'ocr_processing',
                'user_id' => $targetUser->id,
                'enabled' => false,
            ],
            'message' => 'Feature flag disabled for user',
        ]);

    // Verify the flag is disabled for the user
    expect($service->isActive('ocr_processing', $targetUser))->toBe(false);
});

test('admin cannot enable flag for non-existent user', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/admin/feature-flags/ocr_processing/users/99999');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'User not found',
        ]);
});

test('admin cannot enable invalid feature flag for user', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $targetUser = User::factory()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/v1/admin/feature-flags/invalid_flag/users/{$targetUser->id}");

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Feature flag not found',
        ]);
});
