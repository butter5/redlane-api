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
     * These definitions provide the default values when a feature flag
     * has not been explicitly set in the database.
     *
     * @return void
     */
    public static function define(): void
    {
        $defaults = self::defaults();

        foreach ($defaults as $flag => $defaultValue) {
            Feature::define($flag, fn (?User $user) => $defaultValue);
        }
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
