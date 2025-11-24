<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FeatureFlagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feature Flag Controller
 *
 * Handles user-facing feature flag endpoints.
 * Requires authentication via Sanctum.
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
     * Get all feature flags for the authenticated user.
     *
     * Returns all available feature flags with their states for the current user.
     * Flags may be customized per-user or use the global default.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @response 200 {
     *   "data": {
     *     "flags": {
     *       "ocr_processing": false,
     *       "multi_leg_trips": false,
     *       "admin_dashboard": true,
     *       "declaration_export": false,
     *       "currency_api_integration": false
     *     }
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $flags = $this->featureFlagService->allFlags($user);

        return response()->json([
            'data' => [
                'flags' => $flags,
            ],
        ]);
    }
}
