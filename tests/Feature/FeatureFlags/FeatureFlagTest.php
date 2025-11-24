<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated user can get feature flags', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/feature-flags');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'flags' => [
                    'ocr_processing',
                    'multi_leg_trips',
                    'admin_dashboard',
                    'declaration_export',
                    'currency_api_integration',
                ],
            ],
        ]);

    expect($response->json('data.flags'))->toBeArray();
    expect($response->json('data.flags.ocr_processing'))->toBeBool();
});

test('unauthenticated user cannot get feature flags', function () {
    $response = $this->getJson('/api/v1/feature-flags');

    $response->assertStatus(401);
});

test('feature flags reflect user-specific overrides', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($user);

    // Enable a flag for this specific user
    app(\App\Services\FeatureFlagService::class)->enableForUser('ocr_processing', $user);

    $response = $this->getJson('/api/v1/feature-flags');

    $response->assertStatus(200);
    expect($response->json('data.flags.ocr_processing'))->toBe(true);
});

test('feature flags use global defaults when no user override exists', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($user);

    // Set global state
    $service = app(\App\Services\FeatureFlagService::class);
    $service->globalEnable('ocr_processing');

    $response = $this->getJson('/api/v1/feature-flags');

    $response->assertStatus(200);
    expect($response->json('data.flags.ocr_processing'))->toBe(true);
});
