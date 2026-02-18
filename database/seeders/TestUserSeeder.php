<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '+1234567890',
                'password' => Hash::make('Admin@123'),
                'verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');
        $this->command->info('Admin user created: admin@example.com / password');

        // Create Client User
        $client = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Test Client',
                'phone' => '+1234567891',
                'password' => Hash::make('password'),
                'verified_at' => now(),
                'address' => '123 Main Street, City, State 12345',
            ]
        );
        $client->assignRole('client');
        $this->command->info('Client user created: client@example.com / password');

        // Create Vehicle Owner User
        $vehicleOwner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Vehicle Owner',
                'phone' => '+1234567892',
                'password' => Hash::make('password'),
                'verified_at' => now(),
                'address' => '456 Business Park, City, State 12345',
            ]
        );
        $vehicleOwner->assignRole('vehicle_owner');
        $this->command->info('Vehicle Owner user created: owner@example.com / password');

        // Create Driver User
        $driver = User::firstOrCreate(
            ['email' => 'driver@example.com'],
            [
                'name' => 'Test Driver',
                'phone' => '+1234567893',
                'password' => Hash::make('password'),
                'verified_at' => now(),
                'address' => '789 Driver Lane, City, State 12345',
                // Driver-specific fields
                'license_number' => 'DL123456789',
                'license_type' => 'Commercial',
                'license_expiry_date' => now()->addYears(5),
                'hourly_rate' => 25.00,
                'is_available' => true,
                'status' => 'active',
                'total_trips' => 0,
                'rating' => 0,
                'bio' => 'Experienced heavy vehicle driver with 10+ years of experience.',
            ]
        );
        $driver->assignRole('driver');
        $this->command->info('Driver user created: driver@example.com / password');

        // Create another Client for testing
        $client2 = User::firstOrCreate(
            ['email' => 'client2@example.com'],
            [
                'name' => 'Another Client',
                'phone' => '+1234567894',
                'password' => Hash::make('password'),
                'verified_at' => now(),
            ]
        );
        $client2->assignRole('client');
        $this->command->info('Client 2 user created: client2@example.com / password');

        $this->command->info('All test users created successfully!');
        $this->command->info('All users have password: password');
    }
}
