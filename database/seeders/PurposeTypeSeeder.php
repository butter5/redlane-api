<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurposeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'business', 'description' => 'Business'],
            ['code' => 'personal', 'description' => 'Personal'],
            ['code' => 'other', 'description' => 'Other'],
        ];

        foreach ($types as $type) {
            DB::table('purpose_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
