<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'draft', 'description' => 'Draft'],
            ['code' => 'active', 'description' => 'Active'],
            ['code' => 'declared', 'description' => 'Declared'],
            ['code' => 'archived', 'description' => 'Archived'],
        ];

        foreach ($types as $type) {
            DB::table('status_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
