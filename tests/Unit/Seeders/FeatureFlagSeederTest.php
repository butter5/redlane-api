<?php

use Database\Seeders\FeatureFlagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('feature flag seeder seeds all flags', function () {
    // Run the seeder
    $seeder = new FeatureFlagSeeder;
    $seeder->run();

    // Verify all flags have been seeded
    $service = app(\App\Services\FeatureFlagService::class);

    // Check that we can query each flag
    expect($service->isActive('ocr_processing', null))->toBeBool();
    expect($service->isActive('multi_leg_trips', null))->toBeBool();
    expect($service->isActive('admin_dashboard', null))->toBeBool();
    expect($service->isActive('declaration_export', null))->toBeBool();
    expect($service->isActive('currency_api_integration', null))->toBeBool();
});

test('feature flags have correct default states after seeding', function () {
    // Run the seeder
    $seeder = new FeatureFlagSeeder;
    $seeder->run();

    // According to Features::defaults(), admin_dashboard should be true, others false
    $service = app(\App\Services\FeatureFlagService::class);

    expect($service->isActive('ocr_processing', null))->toBe(false);
    expect($service->isActive('multi_leg_trips', null))->toBe(false);
    expect($service->isActive('admin_dashboard', null))->toBe(true);
    expect($service->isActive('declaration_export', null))->toBe(false);
    expect($service->isActive('currency_api_integration', null))->toBe(false);
});

test('seeder can be run multiple times without error', function () {
    $seeder = new FeatureFlagSeeder;

    // Run seeder twice
    $seeder->run();
    $seeder->run();

    // Should still work
    $service = app(\App\Services\FeatureFlagService::class);
    expect($service->isActive('admin_dashboard', null))->toBe(true);
});
