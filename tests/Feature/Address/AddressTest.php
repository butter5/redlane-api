<?php

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create address', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/addresses', [
        'street_line_1' => '123 Main St',
        'street_line_2' => 'Apt 4B',
        'city' => 'New York',
        'state_province' => 'NY',
        'postal_code' => '10001',
        'country_code' => 'US',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'street_line_1',
                'street_line_2',
                'city',
                'state_province',
                'postal_code',
                'country_code',
                'is_primary',
                'created_at',
            ],
        ]);

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'street_line_1' => '123 Main St',
        'city' => 'New York',
    ]);
});

test('first address is automatically set as primary', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/addresses', [
        'street_line_1' => '123 Main St',
        'city' => 'New York',
        'state_province' => 'NY',
        'postal_code' => '10001',
        'country_code' => 'US',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'is_primary' => true,
            ],
        ]);
});

test('authenticated user can list their addresses', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Address::factory()->count(3)->create(['user_id' => $user->id]);
    Address::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/addresses');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('authenticated user can get specific address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/addresses/{$address->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $address->id,
                'street_line_1' => $address->street_line_1,
            ],
        ]);
});

test('user cannot access another users address', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/addresses/{$address->id}");

    $response->assertStatus(403);
});

test('authenticated user can update their address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/api/v1/addresses/{$address->id}", [
        'street_line_1' => '456 Oak Ave',
        'city' => 'Boston',
        'state_province' => 'MA',
        'postal_code' => '02101',
        'country_code' => 'US',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'street_line_1' => '456 Oak Ave',
                'city' => 'Boston',
            ],
        ]);

    $this->assertDatabaseHas('addresses', [
        'id' => $address->id,
        'street_line_1' => '456 Oak Ave',
    ]);
});

test('authenticated user can soft delete their address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id, 'is_primary' => false]);

    $response = $this->actingAs($user)->deleteJson("/api/v1/addresses/{$address->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('addresses', [
        'id' => $address->id,
    ]);
});

test('authenticated user can set address as primary', function () {
    $user = User::factory()->create();
    $address1 = Address::factory()->create(['user_id' => $user->id, 'is_primary' => true]);
    $address2 = Address::factory()->create(['user_id' => $user->id, 'is_primary' => false]);

    $response = $this->actingAs($user)->postJson("/api/v1/addresses/{$address2->id}/set-primary");

    $response->assertStatus(200);

    $address1->refresh();
    $address2->refresh();

    expect($address1->is_primary)->toBeFalse();
    expect($address2->is_primary)->toBeTrue();
});

test('only one address can be primary at a time', function () {
    $user = User::factory()->create();
    $address1 = Address::factory()->create(['user_id' => $user->id, 'is_primary' => true]);
    $address2 = Address::factory()->create(['user_id' => $user->id, 'is_primary' => false]);

    $this->actingAs($user)->postJson("/api/v1/addresses/{$address2->id}/set-primary");

    $user->refresh();
    $primaryAddresses = $user->addresses()->where('is_primary', true)->count();

    expect($primaryAddresses)->toBe(1);
});

test('address creation requires street_line_1', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/addresses', [
        'city' => 'New York',
        'state_province' => 'NY',
        'postal_code' => '10001',
        'country_code' => 'US',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['street_line_1']);
});

test('address creation requires city', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/addresses', [
        'street_line_1' => '123 Main St',
        'state_province' => 'NY',
        'postal_code' => '10001',
        'country_code' => 'US',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['city']);
});

test('address creation requires valid country code', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/addresses', [
        'street_line_1' => '123 Main St',
        'city' => 'New York',
        'state_province' => 'NY',
        'postal_code' => '10001',
        'country_code' => 'USA',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['country_code']);
});

test('unauthenticated user cannot create address', function () {
    $response = $this->postJson('/api/v1/addresses', [
        'street_line_1' => '123 Main St',
        'city' => 'New York',
        'state_province' => 'NY',
        'postal_code' => '10001',
        'country_code' => 'US',
    ]);

    $response->assertStatus(401);
});
