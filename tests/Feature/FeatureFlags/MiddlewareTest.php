<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test route with feature flag middleware
    Route::middleware(['auth:sanctum', 'feature:ocr_processing'])->get('/test-feature-route', function () {
        return response()->json(['message' => 'Feature is enabled']);
    });
});

test('middleware allows access when feature is enabled globally', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($user);

    // Enable the feature globally
    $service = app(\App\Services\FeatureFlagService::class);
    $service->globalEnable('ocr_processing');

    $response = $this->getJson('/test-feature-route');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Feature is enabled']);
});

test('middleware blocks access when feature is disabled globally', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($user);

    // Ensure the feature is disabled globally
    $service = app(\App\Services\FeatureFlagService::class);
    $service->globalDisable('ocr_processing');

    $response = $this->getJson('/test-feature-route');

    $response->assertStatus(403)
        ->assertJson(['message' => 'This feature is not available']);
});

test('middleware allows access when feature is enabled for specific user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($user);

    // Disable globally but enable for this user
    $service = app(\App\Services\FeatureFlagService::class);
    $service->globalDisable('ocr_processing');
    $service->enableForUser('ocr_processing', $user);

    $response = $this->getJson('/test-feature-route');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Feature is enabled']);
});

test('middleware blocks access when feature is disabled for specific user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($user);

    // Enable globally but disable for this user
    $service = app(\App\Services\FeatureFlagService::class);
    $service->globalEnable('ocr_processing');
    $service->disableForUser('ocr_processing', $user);

    $response = $this->getJson('/test-feature-route');

    $response->assertStatus(403)
        ->assertJson(['message' => 'This feature is not available']);
});

test('middleware requires authentication', function () {
    $response = $this->getJson('/test-feature-route');

    $response->assertStatus(401);
});
