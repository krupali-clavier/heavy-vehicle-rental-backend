<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/social-login', [AuthController::class, 'socialLogin']);
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Vehicles
    Route::apiResource('vehicles', VehicleController::class);
    Route::get('vehicles/{vehicle}/availability', [VehicleController::class, 'checkAvailability']);

    // Bookings
    Route::apiResource('bookings', BookingController::class);
    Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Trips
    Route::prefix('trips')->group(function () {
        Route::post('/start', [TripController::class, 'start']);
        Route::post('/end', [TripController::class, 'end']);
        Route::post('/route-point', [TripController::class, 'storeRoutePoint']);
        Route::get('/{trip}', [TripController::class, 'show']);
        Route::get('/{trip}/route', [TripController::class, 'getRoute']);
    });
});
