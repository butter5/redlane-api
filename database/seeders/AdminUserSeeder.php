<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@redlane.local'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
