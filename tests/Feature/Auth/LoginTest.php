<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'email',
                    'first_name',
                    'last_name',
                    'email_verified_at',
                ],
                'token',
            ],
            'message',
        ]);

    expect($response->json('data.user.email'))->toBe('test@example.com');
    expect($response->json('data.token'))->not()->toBeNull();
});

test('login fails with invalid email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});

test('login fails with invalid password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'WrongPassword123!',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});

test('login requires email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login requires password', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('login fails if email is not verified', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
        'email_verified_at' => null,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Email address is not verified',
        ]);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out',
        ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => get_class($user),
    ]);
});

test('logout requires authentication', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
});

test('authenticated user can refresh token', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/auth/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'token',
            ],
            'message',
        ]);

    expect($response->json('data.token'))->not()->toBeNull();
});

test('refresh token requires authentication', function () {
    $response = $this->postJson('/api/v1/auth/refresh');

    $response->assertStatus(401);
});

test('authenticated user can get their details', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => now(),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'email',
                'first_name',
                'last_name',
                'email_verified_at',
                'created_at',
            ],
        ]);

    expect($response->json('data.email'))->toBe('test@example.com');
    expect($response->json('data.first_name'))->toBe('John');
    expect($response->json('data.last_name'))->toBe('Doe');
});

test('me endpoint requires authentication', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(401);
});
