<?php

namespace Database\Seeders;

use App\Features;
use Illuminate\Database\Seeder;
use Laravel\Pennant\Feature;

/**
 * Feature Flag Seeder
 *
 * Seeds initial feature flag states into the database.
 */
class FeatureFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds global feature flags with their default states:
     * - ocr_processing: OFF
     * - multi_leg_trips: OFF
     * - admin_dashboard: ON
     * - declaration_export: OFF
     * - currency_api_integration: OFF
     */
    public function run(): void
    {
        $defaults = Features::defaults();

        foreach ($defaults as $flag => $defaultValue) {
            // Set global default for each feature
            Feature::for(null)->activate($flag);
        }

        $this->command->info('Feature flags seeded successfully.');
    }
}
