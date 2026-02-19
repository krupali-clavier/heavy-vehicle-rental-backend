# Test User Credentials

After running the seeders, the following test users will be available:

## Admin User
- **Email**: `admin@example.com`
- **Password**: `password`
- **Role**: Admin
- **Phone**: +1234567890
- **Status**: Verified

## Client User
- **Email**: `client@example.com`
- **Password**: `password`
- **Role**: Client
- **Phone**: +1234567891
- **Status**: Verified

## Client User 2
- **Email**: `client2@example.com`
- **Password**: `password`
- **Role**: Client
- **Phone**: +1234567894
- **Status**: Verified

## Vehicle Owner User
- **Email**: `owner@example.com`
- **Password**: `password`
- **Role**: Vehicle Owner
- **Phone**: +1234567892
- **Status**: Verified

## Driver User
- **Email**: `driver@example.com`
- **Password**: `password`
- **Role**: Driver
- **Phone**: +1234567893
- **Status**: Verified
- **License Number**: DL123456789
- **License Type**: Commercial
- **Hourly Rate**: $25.00
- **Availability**: Available
- **Status**: Active

## Running the Seeders

```bash
# Run all seeders (roles, permissions, and test users)
php artisan db:seed

# Or run specific seeder
php artisan db:seed --class=TestUserSeeder
```

## Notes

- All users have the same password: `password`
- All users are verified (email and phone)
- Users are created using `firstOrCreate` so running the seeder multiple times won't create duplicates
- The seeder will output information about created users
