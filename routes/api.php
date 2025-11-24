<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Admin\FeatureFlagController as AdminFeatureFlagController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\FeatureFlagController;
use App\Http\Controllers\Api\V1\HouseholdMemberController;
use App\Http\Controllers\Api\V1\ProfileController;
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

        // Profile Management
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);

        // Address Management
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::get('/addresses/{address}', [AddressController::class, 'show']);
        Route::put('/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
        Route::post('/addresses/{address}/set-primary', [AddressController::class, 'setPrimary']);

        // Household Member Management
        Route::get('/household-members', [HouseholdMemberController::class, 'index']);
        Route::post('/household-members', [HouseholdMemberController::class, 'store']);
        Route::get('/household-members/{householdMember}', [HouseholdMemberController::class, 'show']);
        Route::put('/household-members/{householdMember}', [HouseholdMemberController::class, 'update']);
        Route::delete('/household-members/{householdMember}', [HouseholdMemberController::class, 'destroy']);
        Route::post('/household-members/{householdMember}/set-primary-declarant', [HouseholdMemberController::class, 'setPrimaryDeclarant']);
    });

    // Admin feature flag management
    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        Route::get('/feature-flags', [AdminFeatureFlagController::class, 'index']);
        Route::post('/feature-flags/{key}/toggle', [AdminFeatureFlagController::class, 'toggle']);
        Route::post('/feature-flags/{key}/users/{userId}', [AdminFeatureFlagController::class, 'enableForUser']);
        Route::delete('/feature-flags/{key}/users/{userId}', [AdminFeatureFlagController::class, 'disableForUser']);

        // Test route for admin middleware (used in tests)
        Route::get('/test-admin-access', function () {
            return response()->json(['message' => 'Admin access granted']);
        })->middleware('admin');
    });

    // Test route for permission middleware (used in tests)
    Route::get('/test-permission-access', function () {
        return response()->json(['message' => 'Permission granted']);
    })->middleware(['auth:sanctum', 'permission:manage_duty_categories']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
