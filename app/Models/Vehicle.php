<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'type',
        'make',
        'model',
        'year',
        'registration_number',
        'color',
        'hourly_rate',
        'daily_rate',
        'weekly_rate',
        'monthly_rate',
        'requires_driver',
        'is_available',
        'latitude',
        'longitude',
        'location_address',
        'status',
        'availability_calendar',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'daily_rate' => 'decimal:2',
            'weekly_rate' => 'decimal:2',
            'monthly_rate' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'requires_driver' => 'boolean',
            'is_available' => 'boolean',
            'availability_calendar' => 'array',
        ];
    }

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(VehicleImage::class);
    }

    public function documents()
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(VehicleImage::class)->where('is_primary', true);
    }
}
