<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelationshipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'spouse', 'description' => 'Spouse'],
            ['code' => 'child', 'description' => 'Child'],
            ['code' => 'parent', 'description' => 'Parent'],
            ['code' => 'sibling', 'description' => 'Sibling'],
            ['code' => 'other', 'description' => 'Other'],
        ];

        foreach ($types as $type) {
            DB::table('relationship_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
