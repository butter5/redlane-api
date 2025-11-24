<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CalculationMethodTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'percentage', 'description' => 'Percentage'],
            ['code' => 'per_liter', 'description' => 'Per Liter'],
            ['code' => 'per_kilogram', 'description' => 'Per Kilogram'],
            ['code' => 'per_unit', 'description' => 'Per Unit'],
        ];

        foreach ($types as $type) {
            DB::table('calculation_method_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
