<?php

use App\Models\User;
use App\Services\FeatureFlagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(FeatureFlagService::class);
});

test('isActive returns correct state for global flags', function () {
    $this->service->globalEnable('ocr_processing');
    expect($this->service->isActive('ocr_processing', null))->toBe(true);

    $this->service->globalDisable('ocr_processing');
    expect($this->service->isActive('ocr_processing', null))->toBe(false);
});

test('isActive returns correct state for user-specific flags', function () {
    $user = User::factory()->create();

    $this->service->enableForUser('ocr_processing', $user);
    expect($this->service->isActive('ocr_processing', $user))->toBe(true);

    $this->service->disableForUser('ocr_processing', $user);
    expect($this->service->isActive('ocr_processing', $user))->toBe(false);
});

test('user-specific flags override global flags', function () {
    $user = User::factory()->create();

    // Enable globally
    $this->service->globalEnable('ocr_processing');
    expect($this->service->isActive('ocr_processing', $user))->toBe(true);

    // Disable for specific user - should override global
    $this->service->disableForUser('ocr_processing', $user);
    expect($this->service->isActive('ocr_processing', $user))->toBe(false);

    // Global should still be enabled
    expect($this->service->isActive('ocr_processing', null))->toBe(true);
});

test('allFlags returns all feature flags with their states', function () {
    $user = User::factory()->create();

    $flags = $this->service->allFlags($user);

    expect($flags)->toBeArray();
    expect($flags)->toHaveKeys([
        'ocr_processing',
        'multi_leg_trips',
        'admin_dashboard',
        'declaration_export',
        'currency_api_integration',
    ]);
    expect($flags['ocr_processing'])->toBeBool();
});

test('enableForUser enables flag for specific user', function () {
    $user = User::factory()->create();

    $this->service->enableForUser('ocr_processing', $user);

    expect($this->service->isActive('ocr_processing', $user))->toBe(true);
});

test('disableForUser disables flag for specific user', function () {
    $user = User::factory()->create();

    $this->service->enableForUser('ocr_processing', $user);
    $this->service->disableForUser('ocr_processing', $user);

    expect($this->service->isActive('ocr_processing', $user))->toBe(false);
});

test('globalEnable enables flag globally', function () {
    $this->service->globalEnable('ocr_processing');

    expect($this->service->isActive('ocr_processing', null))->toBe(true);
});

test('globalDisable disables flag globally', function () {
    $this->service->globalEnable('ocr_processing');
    $this->service->globalDisable('ocr_processing');

    expect($this->service->isActive('ocr_processing', null))->toBe(false);
});

test('getFlagStats returns statistics for all flags', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Enable for specific users
    $this->service->enableForUser('ocr_processing', $user1);
    $this->service->enableForUser('ocr_processing', $user2);

    $stats = $this->service->getFlagStats();

    expect($stats)->toBeArray();
    expect($stats)->toHaveKey('ocr_processing');
    expect($stats['ocr_processing'])->toHaveKeys(['global', 'user_overrides']);
    expect($stats['ocr_processing']['global'])->toBeBool();
    expect($stats['ocr_processing']['user_overrides'])->toBeInt();
});

test('isValidFlag returns true for valid flags', function () {
    expect($this->service->isValidFlag('ocr_processing'))->toBe(true);
    expect($this->service->isValidFlag('multi_leg_trips'))->toBe(true);
    expect($this->service->isValidFlag('admin_dashboard'))->toBe(true);
});

test('isValidFlag returns false for invalid flags', function () {
    expect($this->service->isValidFlag('invalid_flag'))->toBe(false);
    expect($this->service->isValidFlag('non_existent'))->toBe(false);
});

test('multiple users can have different flag states', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->service->enableForUser('ocr_processing', $user1);
    $this->service->disableForUser('ocr_processing', $user2);

    expect($this->service->isActive('ocr_processing', $user1))->toBe(true);
    expect($this->service->isActive('ocr_processing', $user2))->toBe(false);
});
