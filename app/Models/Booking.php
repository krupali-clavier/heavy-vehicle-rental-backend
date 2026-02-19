<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'vehicle_id',
        'driver_id',
        'pickup_type',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_address',
        'start_date',
        'end_date',
        'rental_type',
        'rental_amount',
        'driver_fee',
        'delivery_fee',
        'platform_fee',
        'total_amount',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'special_instructions',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'cancelled_at' => 'datetime',
            'pickup_latitude' => 'decimal:8',
            'pickup_longitude' => 'decimal:8',
            'delivery_latitude' => 'decimal:8',
            'delivery_longitude' => 'decimal:8',
            'rental_amount' => 'decimal:2',
            'driver_fee' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function trip()
    {
        return $this->hasOne(Trip::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
