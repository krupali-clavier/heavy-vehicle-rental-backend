# Migration Structure Overview

## User Management

### ✅ Single Users Table
- **File**: `0001_01_01_000000_create_users_table.php`
- **Purpose**: All users (clients, vehicle owners, drivers, admins) are stored here
- **Status**: ✅ Correct - Single source of truth for all users

### ✅ User Profile Fields
- **File**: `2026_02_17_044348_add_role_fields_to_users_table.php`
- **Purpose**: Adds phone, OTP, address, profile_image to users table
- **Note**: Role enum field has been removed (now managed by Spatie)
- **Status**: ✅ Correct - No role enum, using Spatie

### ✅ Spatie Permission Tables
- **File**: `2026_02_17_051326_create_permission_tables.php`
- **Purpose**: Creates roles, permissions, and pivot tables for Spatie
- **Status**: ✅ Correct - Manages roles and permissions

## Profile Extensions (Not User Tables)

### ✅ Drivers Table (Profile Extension)
- **File**: `2026_02_17_044353_create_drivers_table.php`
- **Purpose**: Profile extension for users with 'driver' role
- **Relationship**: `user_id` → `users.id` (one-to-one)
- **Contains**: License info, rates, availability, status
- **Status**: ✅ Correct - This is NOT a user table, it's a profile extension
- **Why Keep**: Drivers need additional profile data (license, rates, etc.)

## Core Business Tables

### ✅ Vehicles
- **File**: `2026_02_17_044352_create_vehicles_table.php`
- **References**: `owner_id` → `users.id`
- **Status**: ✅ Correct

### ✅ Bookings
- **File**: `2026_02_17_044353_create_bookings_table.php`
- **References**: 
  - `client_id` → `users.id`
  - `driver_id` → `drivers.id` (profile extension)
- **Status**: ✅ Correct

### ✅ Trips
- **File**: `2026_02_17_044353_create_trips_table.php`
- **References**: `driver_id` → `drivers.id` (profile extension)
- **Status**: ✅ Correct

### ✅ Other Tables
- Payments, Invoices, Route Points, Vehicle Images/Documents
- **Status**: ✅ All correct

## Summary

**All users are in the `users` table** ✅
- Clients, Vehicle Owners, Drivers, Admins - all in same table
- Roles managed by Spatie Laravel Permission
- No separate user tables

**Drivers table is a profile extension** ✅
- NOT a user table
- One-to-one relationship with users
- Stores driver-specific data (license, rates, etc.)
- Required for the application

**No redundant migrations** ✅
- All migrations serve a purpose
- No duplicate user management
- Clean structure
