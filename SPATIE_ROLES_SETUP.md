# Spatie Laravel Permission Setup

## Overview

This project uses [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) for role and permission management instead of a simple enum-based role system.

## Installation

The package has been installed and configured. Run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

## Roles

The following roles are created by the seeder:

1. **client** - Regular users who rent vehicles
2. **vehicle_owner** - Users who own and list vehicles
3. **driver** - Users who drive vehicles
4. **admin** - Platform administrators with full access

## Permissions

Permissions are organized by feature:

### Vehicle Permissions
- `view vehicles`
- `create vehicles`
- `edit vehicles`
- `delete vehicles`
- `manage own vehicles`

### Booking Permissions
- `view bookings`
- `create bookings`
- `edit bookings`
- `cancel bookings`
- `manage own bookings`
- `accept bookings`
- `reject bookings`

### Trip Permissions
- `view trips`
- `start trips`
- `end trips`
- `track trips`
- `view trip routes`

### Driver Permissions
- `view drivers`
- `create drivers`
- `edit drivers`
- `manage driver profile`

### Payment & Invoice Permissions
- `view payments`
- `process payments`
- `refund payments`
- `view invoices`
- `create invoices`
- `download invoices`

### Admin Permissions
- `manage users`
- `manage all vehicles`
- `manage all bookings`
- `manage all trips`
- `manage roles`
- `manage permissions`
- `view analytics`
- `manage settings`

## Usage

### Assigning Roles

```php
// Assign a role to a user
$user->assignRole('client');
$user->assignRole('vehicle_owner');

// Assign multiple roles
$user->assignRole(['client', 'vehicle_owner']);

// Remove a role
$user->removeRole('client');

// Sync roles (removes all existing roles and assigns new ones)
$user->syncRoles(['admin']);
```

### Checking Roles

```php
// Check if user has a role
if ($user->hasRole('admin')) {
    // User is an admin
}

// Check if user has any of the given roles
if ($user->hasAnyRole(['admin', 'vehicle_owner'])) {
    // User is admin or vehicle owner
}

// Check if user has all of the given roles
if ($user->hasAllRoles(['client', 'driver'])) {
    // User has both client and driver roles
}

// Using helper methods (defined in User model)
$user->isClient();        // Returns true if has 'client' role
$user->isVehicleOwner();  // Returns true if has 'vehicle_owner' role
$user->isDriver();        // Returns true if has 'driver' role
$user->isAdmin();         // Returns true if has 'admin' role
```

### Assigning Permissions

```php
// Assign permission directly to user
$user->givePermissionTo('edit vehicles');

// Assign permission via role
$role = Role::findByName('vehicle_owner');
$role->givePermissionTo('manage own vehicles');

// Check if user has permission
if ($user->can('edit vehicles')) {
    // User can edit vehicles
}

// Check if user has permission via role
if ($user->hasPermissionTo('manage own vehicles')) {
    // User has permission
}
```

### Using in Controllers

```php
// Check role in controller
public function index()
{
    if (!auth()->user()->hasRole('admin')) {
        abort(403, 'Unauthorized');
    }
    // ...
}

// Using middleware
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Admin only routes
});

// Using permission middleware
Route::middleware(['auth:api', 'permission:manage users'])->group(function () {
    // Users with 'manage users' permission
});
```

### Using in Middleware

The `RoleMiddleware` has been updated to use Spatie:

```php
// In routes/api.php
Route::middleware(['auth:api', 'role:admin,vehicle_owner'])->group(function () {
    // Routes accessible by admin or vehicle_owner
});
```

### JWT Token Claims

The JWT token now includes roles and permissions:

```json
{
  "roles": ["client"],
  "permissions": ["view vehicles", "create bookings", ...]
}
```

## Migration from Old Role System

If you have existing users with the old `role` enum field:

1. The migration has been updated to remove the `role` enum field
2. Run the seeder to create roles: `php artisan db:seed --class=RolePermissionSeeder`
3. Migrate existing users:

```php
// In a migration or seeder
User::where('role', 'client')->get()->each(function ($user) {
    $user->assignRole('client');
});

User::where('role', 'vehicle_owner')->get()->each(function ($user) {
    $user->assignRole('vehicle_owner');
});

User::where('role', 'driver')->get()->each(function ($user) {
    $user->assignRole('driver');
});

User::where('role', 'admin')->get()->each(function ($user) {
    $user->assignRole('admin');
});
```

## Database Tables

Spatie creates the following tables:
- `roles` - Stores roles
- `permissions` - Stores permissions
- `model_has_roles` - Pivot table for user-role relationships
- `model_has_permissions` - Pivot table for user-permission relationships
- `role_has_permissions` - Pivot table for role-permission relationships

## Configuration

Configuration file: `config/permission.php`

Key settings:
- `models.role` - Role model class
- `models.permission` - Permission model class
- `cache.expiration_time` - Cache expiration for permissions
- `table_names` - Custom table names if needed

## Best Practices

1. **Use Roles for Groups**: Assign permissions to roles, not individual users
2. **Use Permissions for Actions**: Create granular permissions for specific actions
3. **Cache Permissions**: Spatie caches permissions by default for performance
4. **Clear Cache**: Clear cache after role/permission changes: `php artisan permission:cache-reset`
5. **Use Middleware**: Use role/permission middleware in routes for access control

## Commands

```bash
# Clear permission cache
php artisan permission:cache-reset

# Create a role
php artisan tinker
>>> Role::create(['name' => 'moderator']);

# Create a permission
>>> Permission::create(['name' => 'moderate content']);
```
