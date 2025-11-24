<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'manage_duty_categories',
            'manage_currencies',
            'manage_users',
            'view_all_declarations',
            'manage_feature_flags',
            'view_audit_logs',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        // Admin role - has all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        // User role - no special permissions (just their own data)
        Role::firstOrCreate(['name' => 'user']);

        // Customs Officer role - can view all declarations
        $customsOfficerRole = Role::firstOrCreate(['name' => 'customs_officer']);
        $customsOfficerRole->syncPermissions(['view_all_declarations']);
    }
}
