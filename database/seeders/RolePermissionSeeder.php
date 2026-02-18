<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Set the guard name (use 'web' as default guard)
        $guardName = 'web';

        // Create permissions
        $permissions = [
            // Vehicle permissions
            'view_vehicles',
            'create_vehicles',
            'edit_vehicles',
            'delete_vehicles',
            'edit_own_vehicles',
            'delete_own_vehicles',
            'manage_own_vehicles',

            // Booking permissions
            'view_bookings',
            'view_own_bookings',
            'view_assigned_bookings',
            'create_bookings',
            'edit_bookings',
            'cancel_bookings',
            'cancel_own_bookings',
            'manage_own_bookings',
            'accept_bookings',
            'reject_bookings',

            // Trip permissions
            'view_trips',
            'view_own_trips',
            'start_trips',
            'end_trips',
            'track_trips',
            'view_trip_routes',

            // Driver permissions
            'view_drivers',
            'create_drivers',
            'edit_drivers',
            'manage_driver_profile',

            // Payment permissions
            'view_payments',
            'view_own_payments',
            'process_payments',
            'refund_payments',

            // Invoice permissions
            'view_invoices',
            'view_own_invoices',
            'create_invoices',
            'download_invoices',
            'download_own_invoices',

            // Admin permissions
            'manage_users',
            'manage_all_vehicles',
            'manage_all_bookings',
            'manage_all_trips',
            'manage_roles',
            'manage_permissions',
            'view_analytics',
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guardName]);
        }

        // Create roles and assign permissions
        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => $guardName]);
        $clientRole->givePermissionTo([
            'view_vehicles',
            'create_bookings',
            'view_own_bookings',
            'cancel_own_bookings',
            'view_own_trips',
            'view_own_payments',
            'view_own_invoices',
            'download_own_invoices',
        ]);

        $vehicleOwnerRole = Role::create(['name' => 'vehicle_owner', 'guard_name' => $guardName]);
        $vehicleOwnerRole->givePermissionTo([
            'view_vehicles',
            'create_vehicles',
            'edit_own_vehicles',
            'delete_own_vehicles',
            'manage_own_vehicles',
            'view_bookings',
            'view_own_bookings',
            'accept_bookings',
            'reject_bookings',
            'view_own_trips',
            'view_own_payments',
            'view_own_invoices',
        ]);

        $driverRole = Role::create(['name' => 'driver', 'guard_name' => $guardName]);
        $driverRole->givePermissionTo([
            'view_vehicles',
            'view_bookings',
            'view_assigned_bookings',
            'view_trips',
            'start_trips',
            'end_trips',
            'track_trips',
            'view_trip_routes',
            'manage_driver_profile',
            'view_own_payments',
        ]);

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => $guardName]);
        $adminRole->givePermissionTo(Permission::where('guard_name', $guardName)->get());
    }
}
