# User Structure Documentation

## Overview

All users (clients, vehicle owners, drivers, and admins) are stored in the **single `users` table**. Roles are managed by **Spatie Laravel Permission** package.

## User Roles

Roles are managed by Spatie (not an enum field). Available roles:

- `client` - Regular users who rent vehicles
- `vehicle_owner` - Users who own and list vehicles
- `driver` - Users who drive vehicles (with driver-specific fields in users table)
- `admin` - Platform administrators

## Database Structure

### Users Table

All user accounts are stored here with:

- Basic info: `name`, `email`, `phone`, `password`
- Roles: Managed by Spatie Laravel Permission (no enum field)
- Verification:`verified_at`
- Profile: `address`, `profile_image`
- OTP: `otp`, `otp_expires_at` (for phone verification)
- Driver fields: `license_number`, `license_type`, `license_expiry_date`, `license_image`, `hourly_rate`, `is_available`, `status`, `total_trips`, `rating`, `bio` (nullable, only for drivers)

### Driver Fields in Users Table

Driver-specific fields are stored directly in the `users` table (nullable fields):

- `license_number` - Driver license number
- `license_type` - Type of license
- `license_expiry_date` - License expiration date
- `license_image` - License document image
- `hourly_rate` - Driver hourly rate
- `is_available` - Driver availability status
- `status` - Status: pending, approved, rejected, suspended
- `total_trips` - Total number of trips completed
- `rating` - Driver rating
- `bio` - Driver bio/description

**Important**: These fields are nullable and only populated for users with the 'driver' role. All users are still in the same `users` table.

## Relationships

### User Model

```php
// Check user role
$user->isClient()        // Returns true if role is 'client'
$user->isVehicleOwner() // Returns true if role is 'vehicle_owner'
$user->isDriver()       // Returns true if role is 'driver'
$user->isAdmin()        // Returns true if role is 'admin'

// Get driver profile (if user is a driver)
$user->driver           // Returns Driver model or null
$user->hasDriverProfile() // Returns true if user has driver profile
```

### Booking Model

```php
$booking->client        // User model (the client who made the booking)
$booking->driver        // Driver model (the driver profile)
$booking->driverUser    // User model (the driver user account)
```

### Trip Model

```php
$trip->driver           // Driver model (the driver profile)
$trip->driverUser       // User model (the driver user account)
```

## Examples

### Creating a Driver User

```php
// Create user with driver role and driver-specific data
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'password' => Hash::make('password'),
    'license_number' => 'DL123456',
    'license_type' => 'Commercial',
    'license_expiry_date' => '2025-12-31',
    'hourly_rate' => 25.00,
    'status' => 'pending',
]);

// Assign driver role using Spatie
$user->assignRole('driver');
```

### Accessing Driver Information

```php
// From a user
$user = User::find(1);
if ($user->isDriver()) {
    $licenseNumber = $user->license_number;
    $hourlyRate = $user->hourly_rate;
    $driverStatus = $user->status;
}

// From a booking
$booking = Booking::find(1);
$driver = $booking->driver; // User model (if driver assigned)
if ($driver) {
    $licenseNumber = $driver->license_number;
}
```

## Key Points

1. **Single Source of Truth**: All user accounts are in the `users` table
2. **Role-Based Access**: Use Spatie roles to determine user type (`$user->hasRole('driver')`)
3. **Driver Data in Users Table**: Driver-specific fields are stored directly in `users` table (nullable)
4. **Consistent Authentication**: All users authenticate the same way via the `users` table
5. **Flexible Roles**: Spatie supports multiple roles per user if needed

## Migration Notes

When creating users:

- Always create them in the `users` table
- Assign roles using Spatie: `$user->assignRole('driver')`
- If role is 'driver', populate driver-specific fields (license_number, hourly_rate, etc.)
- All driver data is stored directly in the `users` table (no separate drivers table)
