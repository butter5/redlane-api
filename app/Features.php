<?php

namespace App;

use App\Models\User;
use Illuminate\Support\Lottery;
use Laravel\Pennant\Feature;

/**
 * Feature Flag Definitions
 *
 * This class defines all feature flags available in the application.
 * Each feature flag can be scoped globally or per-user.
 */
class Features
{
    /**
     * Define all feature flags for the application.
     *
     * @return void
     */
    public static function define(): void
    {
        // OCR Processing - Disabled by default
        Feature::define('ocr_processing', fn (?User $user) => false);

        // Multi-leg Trips - Disabled by default
        Feature::define('multi_leg_trips', fn (?User $user) => false);

        // Admin Dashboard - Enabled by default
        Feature::define('admin_dashboard', fn (?User $user) => true);

        // Declaration Export - Disabled by default
        Feature::define('declaration_export', fn (?User $user) => false);

        // Currency API Integration - Disabled by default
        Feature::define('currency_api_integration', fn (?User $user) => false);
    }

    /**
     * List of all feature flags with their default states.
     *
     * @return array<string, bool>
     */
    public static function defaults(): array
    {
        return [
            'ocr_processing' => false,
            'multi_leg_trips' => false,
            'admin_dashboard' => true,
            'declaration_export' => false,
            'currency_api_integration' => false,
        ];
    }

    /**
     * Get all feature flag keys.
     *
     * @return array<string>
     */
    public static function keys(): array
    {
        return array_keys(self::defaults());
    }
}
