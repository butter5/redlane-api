<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FeatureFlagService;
use Illuminate\Http\JsonResponse;

/**
 * Admin Feature Flag Controller
 *
 * Handles administrative feature flag management endpoints.
 * All endpoints require admin authentication.
 */
class FeatureFlagController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected FeatureFlagService $featureFlagService
    ) {}

    /**
     * Get all feature flags with statistics.
     *
     * Returns all feature flags with their global state and number of user-specific overrides.
     *
     *
     * @response 200 {
     *   "data": {
     *     "flags": {
     *       "ocr_processing": {
     *         "global": false,
     *         "user_overrides": 3
     *       },
     *       "admin_dashboard": {
     *         "global": true,
     *         "user_overrides": 0
     *       }
     *     }
     *   }
     * }
     */
    public function index(): JsonResponse
    {
        $stats = $this->featureFlagService->getFlagStats();

        return response()->json([
            'data' => [
                'flags' => $stats,
            ],
        ]);
    }

    /**
     * Toggle a feature flag globally.
     *
     * Enables or disables a feature flag at the global level.
     * This does not affect user-specific overrides.
     *
     * @param  string  $key  The feature flag key
     *
     * @response 200 {
     *   "data": {
     *     "flag": "ocr_processing",
     *     "enabled": true
     *   },
     *   "message": "Feature flag toggled successfully"
     * }
     * @response 404 {
     *   "message": "Feature flag not found"
     * }
     */
    public function toggle(string $key): JsonResponse
    {
        if (! $this->featureFlagService->isValidFlag($key)) {
            return response()->json([
                'message' => 'Feature flag not found',
            ], 404);
        }

        $currentState = $this->featureFlagService->isActive($key, null);

        if ($currentState) {
            $this->featureFlagService->globalDisable($key);
            $newState = false;
        } else {
            $this->featureFlagService->globalEnable($key);
            $newState = true;
        }

        return response()->json([
            'data' => [
                'flag' => $key,
                'enabled' => $newState,
            ],
            'message' => 'Feature flag toggled successfully',
        ]);
    }

    /**
     * Enable a feature flag for a specific user.
     *
     * Creates a user-specific override that enables the feature for that user,
     * regardless of the global state.
     *
     * @param  string  $key  The feature flag key
     * @param  int  $userId  The user ID
     *
     * @response 200 {
     *   "data": {
     *     "flag": "ocr_processing",
     *     "user_id": 123,
     *     "enabled": true
     *   },
     *   "message": "Feature flag enabled for user"
     * }
     * @response 404 {
     *   "message": "Feature flag not found"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function enableForUser(string $key, int $userId): JsonResponse
    {
        if (! $this->featureFlagService->isValidFlag($key)) {
            return response()->json([
                'message' => 'Feature flag not found',
            ], 404);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $this->featureFlagService->enableForUser($key, $user);

        return response()->json([
            'data' => [
                'flag' => $key,
                'user_id' => $userId,
                'enabled' => true,
            ],
            'message' => 'Feature flag enabled for user',
        ]);
    }

    /**
     * Disable a feature flag for a specific user.
     *
     * Creates a user-specific override that disables the feature for that user,
     * regardless of the global state.
     *
     * @param  string  $key  The feature flag key
     * @param  int  $userId  The user ID
     *
     * @response 200 {
     *   "data": {
     *     "flag": "ocr_processing",
     *     "user_id": 123,
     *     "enabled": false
     *   },
     *   "message": "Feature flag disabled for user"
     * }
     * @response 404 {
     *   "message": "Feature flag not found"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function disableForUser(string $key, int $userId): JsonResponse
    {
        if (! $this->featureFlagService->isValidFlag($key)) {
            return response()->json([
                'message' => 'Feature flag not found',
            ], 404);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $this->featureFlagService->disableForUser($key, $user);

        return response()->json([
            'data' => [
                'flag' => $key,
                'user_id' => $userId,
                'enabled' => false,
            ],
            'message' => 'Feature flag disabled for user',
        ]);
    }
}
