<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The guard name for Spatie Permission
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'license_expiry_date' => 'date',
            'password' => 'hashed',
            'is_available' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'rating' => 'decimal:2',
            'total_trips' => 'integer',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [
            'roles' => $this->getRoleNames()->toArray(),
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray(),
        ];
    }

    // Relationships
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    public function driverBookings()
    {
        return $this->hasMany(Booking::class, 'driver_id');
    }

    public function driverTrips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function otps()
    {
        return $this->hasMany(Otp::class);
    }

    // Helper methods (using Spatie roles)
    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    public function isVehicleOwner(): bool
    {
        return $this->hasRole('vehicle_owner');
    }

    public function isDriver(): bool
    {
        return $this->hasRole('driver');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user has driver profile (has driver-specific data)
     */
    public function hasDriverProfile(): bool
    {
        return $this->isDriver() && $this->license_number !== null;
    }
}
