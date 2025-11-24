<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'liters', 'abbreviation' => 'L', 'description' => 'Liters'],
            ['code' => 'kilograms', 'abbreviation' => 'kg', 'description' => 'Kilograms'],
            ['code' => 'units', 'abbreviation' => 'unit', 'description' => 'Units'],
            ['code' => 'cigars', 'abbreviation' => 'cigar', 'description' => 'Cigars'],
            ['code' => 'cigarettes', 'abbreviation' => 'cigarette', 'description' => 'Cigarettes'],
        ];

        foreach ($types as $type) {
            DB::table('unit_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
