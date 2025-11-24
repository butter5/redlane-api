<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('user can register with valid data', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+1234567890',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'email',
                    'first_name',
                    'last_name',
                    'phone',
                    'email_verified_at',
                    'created_at',
                ],
                'token',
            ],
            'message',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+1234567890',
    ]);

    $user = User::where('email', 'test@example.com')->first();
    expect(Hash::check('Password123!', $user->password))->toBeTrue();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('registration requires email', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('registration requires valid email format', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'invalid-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('registration requires unique email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'existing@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('registration requires password', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('registration requires password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('registration requires matching password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('registration requires minimum password length', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Pass1!',
        'password_confirmation' => 'Pass1!',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('registration requires first name', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});

test('registration requires last name', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'first_name' => 'John',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name']);
});

test('registration works without phone', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'phone' => null,
    ]);
});
