<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('user can request password reset', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Password reset link sent to your email',
        ]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('forgot password requires email', function () {
    $response = $this->postJson('/api/v1/auth/forgot-password', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('forgot password requires valid email format', function () {
    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('forgot password returns success even for non-existent email', function () {
    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Password reset link sent to your email',
        ]);
});

test('user can reset password with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('OldPassword123!'),
    ]);

    $token = Password::createToken($user);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Password has been reset successfully',
        ]);

    $user->refresh();
    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
});

test('password reset requires email', function () {
    $response = $this->postJson('/api/v1/auth/reset-password', [
        'token' => 'some-token',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('password reset requires token', function () {
    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'test@example.com',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token']);
});

test('password reset requires password', function () {
    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => 'some-token',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('password reset requires password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => 'some-token',
        'password' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('password reset requires matching password confirmation', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Password::createToken($user);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'DifferentPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('password reset fails with invalid token', function () {
    User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => 'invalid-token',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Invalid or expired password reset token',
        ]);
});

test('password reset requires minimum password length', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Password::createToken($user);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'Pass1!',
        'password_confirmation' => 'Pass1!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
