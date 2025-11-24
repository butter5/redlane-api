<?php

namespace App\Http\Middleware;

use App\Services\FeatureFlagService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Feature Flag Middleware
 *
 * Middleware to check if a feature flag is enabled before allowing access to a route.
 * Can be applied to routes or route groups.
 *
 * Usage:
 * Route::get('/endpoint', [Controller::class, 'method'])
 *     ->middleware('feature:ocr_processing');
 */
class CheckFeatureFlag
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected FeatureFlagService $featureFlagService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $flag The feature flag key to check
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        $user = $request->user();

        if (! $this->featureFlagService->isActive($flag, $user)) {
            return response()->json([
                'message' => 'This feature is not available',
            ], 403);
        }

        return $next($request);
    }
}
