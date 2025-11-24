<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated user can get their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/profile');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'email',
                'first_name',
                'last_name',
                'phone',
                'email_verified_at',
                'created_at',
            ],
        ]);
});

test('unauthenticated user cannot get profile', function () {
    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(401);
});

test('authenticated user can update their profile', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+1234567890',
    ]);

    $response = $this->actingAs($user)->putJson('/api/v1/profile', [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'phone' => '+0987654321',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'phone' => '+0987654321',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'phone' => '+0987654321',
    ]);
});

test('profile update requires first name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/api/v1/profile', [
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});

test('profile update requires last name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/api/v1/profile', [
        'first_name' => 'John',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name']);
});

test('authenticated user can change password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/profile/change-password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Password changed successfully',
        ]);

    $user->refresh();
    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
});

test('change password requires current password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/profile/change-password', [
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

test('change password validates current password is correct', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/profile/change-password', [
        'current_password' => 'WrongPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

test('change password requires password confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/profile/change-password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('change password requires matching confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/profile/change-password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'DifferentPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
