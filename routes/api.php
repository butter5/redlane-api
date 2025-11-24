<?php

use App\Http\Controllers\Api\V1\Admin\FeatureFlagController as AdminFeatureFlagController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\FeatureFlagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'Red Lane API',
        'version' => '1.0.0',
    ]);
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:auth');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);

            Route::post('/email/resend', [AuthController::class, 'resendVerification'])
                ->name('verification.send');
        });
    });

    // Feature flags for authenticated users
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/feature-flags', [FeatureFlagController::class, 'index']);
    });

    // Admin feature flag management
    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        Route::get('/feature-flags', [AdminFeatureFlagController::class, 'index']);
        Route::post('/feature-flags/{key}/toggle', [AdminFeatureFlagController::class, 'toggle']);
        Route::post('/feature-flags/{key}/users/{userId}', [AdminFeatureFlagController::class, 'enableForUser']);
        Route::delete('/feature-flags/{key}/users/{userId}', [AdminFeatureFlagController::class, 'disableForUser']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
