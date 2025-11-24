<?php

namespace App\Services;

use App\Features;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

/**
 * Feature Flag Service
 *
 * Provides methods for managing feature flags at both global and user levels.
 * Feature flags are stored in the database and cached for performance.
 */
class FeatureFlagService
{
    /**
     * Check if a feature flag is active for a given user.
     *
     * @param string $flag The feature flag key
     * @param User|null $user The user to check (null for global scope)
     * @return bool True if the feature is active, false otherwise
     */
    public function isActive(string $flag, ?User $user = null): bool
    {
        return Feature::for($user)->active($flag);
    }

    /**
     * Get all feature flags and their states for a given user.
     *
     * @param User|null $user The user to check (null for global scope)
     * @return array<string, bool> Array of feature flags with their states
     */
    public function allFlags(?User $user = null): array
    {
        $flags = [];
        foreach (Features::keys() as $flag) {
            $flags[$flag] = $this->isActive($flag, $user);
        }
        return $flags;
    }

    /**
     * Enable a feature flag for a specific user.
     *
     * @param string $flag The feature flag key
     * @param User $user The user to enable the flag for
     * @return void
     */
    public function enableForUser(string $flag, User $user): void
    {
        Feature::for($user)->activate($flag);
    }

    /**
     * Disable a feature flag for a specific user.
     *
     * @param string $flag The feature flag key
     * @param User $user The user to disable the flag for
     * @return void
     */
    public function disableForUser(string $flag, User $user): void
    {
        Feature::for($user)->deactivate($flag);
    }

    /**
     * Enable a feature flag globally.
     *
     * @param string $flag The feature flag key
     * @return void
     */
    public function globalEnable(string $flag): void
    {
        Feature::for(null)->activate($flag);
    }

    /**
     * Disable a feature flag globally.
     *
     * @param string $flag The feature flag key
     * @return void
     */
    public function globalDisable(string $flag): void
    {
        Feature::for(null)->deactivate($flag);
    }

    /**
     * Get statistics for all feature flags.
     * Returns global state and count of users with overrides.
     *
     * @return array<string, array{global: bool, user_overrides: int}>
     */
    public function getFlagStats(): array
    {
        $stats = [];
        
        foreach (Features::keys() as $flag) {
            $globalState = Feature::for(null)->active($flag);
            
            // Count user-specific overrides
            $userOverrides = DB::table('features')
                ->where('name', $flag)
                ->where('scope', '!=', 'default')
                ->count();
            
            $stats[$flag] = [
                'global' => $globalState,
                'user_overrides' => $userOverrides,
            ];
        }
        
        return $stats;
    }

    /**
     * Check if a feature flag key is valid.
     *
     * @param string $flag The feature flag key to validate
     * @return bool True if the flag exists, false otherwise
     */
    public function isValidFlag(string $flag): bool
    {
        return in_array($flag, Features::keys(), true);
    }
}
