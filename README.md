# Heavy Vehicle Rental Platform - Backend API

A comprehensive Laravel-based backend API for a heavy vehicle rental marketplace platform.

## Overview

This platform enables:
- **Clients** to rent heavy vehicles with or without drivers
- **Vehicle Owners** to monetize their assets
- **Drivers** to manage trips and share GPS location
- **Admins** to manage operations, tracking, and monetization

## Tech Stack

- **Framework**: Laravel 12
- **Authentication**: JWT (tymon/jwt-auth)
- **Database**: MySQL
- **Real-time**: WebSocket support (Pusher ready)
- **API**: RESTful API

## Features

### MVP Features
- ✅ User authentication with JWT (email/phone + OTP)
- ✅ Role-based access control (Client, Vehicle Owner, Driver, Admin)
- ✅ Vehicle management (CRUD, images, documents, availability)
- ✅ Booking system with pickup/delivery options
- ✅ Trip management with GPS tracking
- ✅ Payment and invoicing structure
- ✅ Real-time GPS tracking support

### Database Schema
- Users (with role-based fields)
- Vehicles (with pricing, availability, location)
- Vehicle Images & Documents
- Drivers (with license verification)
- Bookings (with status lifecycle)
- Trips (with GPS tracking)
- Route Points (GPS coordinates)
- Payments (with gateway integration)
- Invoices (automated generation)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd heavy-vehicle-rental-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Configure database**
   Update `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=heavy_vehicle_rental
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the server**
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login with email/password
- `POST /api/auth/send-otp` - Send OTP to phone
- `POST /api/auth/verify-otp` - Verify OTP and get token
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/me` - Get authenticated user
- `POST /api/auth/refresh` - Refresh JWT token

### Vehicles
- `GET /api/vehicles` - List vehicles
- `POST /api/vehicles` - Create vehicle (Vehicle Owner)
- `GET /api/vehicles/{id}` - Get vehicle details
- `PUT /api/vehicles/{id}` - Update vehicle
- `DELETE /api/vehicles/{id}` - Delete vehicle
- `GET /api/vehicles/{id}/availability` - Check availability

### Bookings
- `GET /api/bookings` - List bookings
- `POST /api/bookings` - Create booking
- `GET /api/bookings/{id}` - Get booking details
- `PATCH /api/bookings/{id}/status` - Update booking status
- `POST /api/bookings/{id}/cancel` - Cancel booking

### Trips
- `POST /api/trips/start` - Start trip
- `POST /api/trips/end` - End trip
- `POST /api/trips/route-point` - Store GPS route point
- `GET /api/trips/{id}` - Get trip details
- `GET /api/trips/{id}/route` - Get trip route history

## User Roles

- **client**: Can browse vehicles, create bookings, track trips
- **vehicle_owner**: Can manage vehicles, accept/reject bookings
- **driver**: Can view assigned bookings, start/complete trips, share GPS
- **admin**: Full access to manage all entities

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── VehicleController.php
│   │       ├── BookingController.php
│   │       └── TripController.php
│   └── Middleware/
│       └── RoleMiddleware.php
├── Models/
│   ├── User.php
│   ├── Vehicle.php
│   ├── Driver.php
│   ├── Booking.php
│   ├── Trip.php
│   ├── RoutePoint.php
│   ├── Payment.php
│   └── Invoice.php
database/
└── migrations/
    ├── create_users_table.php
    ├── create_vehicles_table.php
    ├── create_bookings_table.php
    └── ...
routes/
└── api.php
```

## Next Steps

- [ ] Implement service classes for business logic
- [ ] Set up WebSocket server for real-time GPS tracking
- [ ] Integrate payment gateways (Stripe/Razorpay)
- [ ] Create seeders and factories for testing
- [ ] Implement notification system
- [ ] Add API documentation (Swagger/Postman)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
