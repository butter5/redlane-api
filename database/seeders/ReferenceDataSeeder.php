<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Seed all reference/lookup tables.
     */
    public function run(): void
    {
        $this->call([
            RelationshipTypeSeeder::class,
            CalculationMethodTypeSeeder::class,
            UnitTypeSeeder::class,
            StatusTypeSeeder::class,
            PurposeTypeSeeder::class,
        ]);
    }
}
