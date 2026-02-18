<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'driver_id',
        'started_at',
        'ended_at',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'total_distance',
        'total_duration',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'start_latitude' => 'decimal:8',
            'start_longitude' => 'decimal:8',
            'end_latitude' => 'decimal:8',
            'end_longitude' => 'decimal:8',
            'total_distance' => 'decimal:2',
            'total_duration' => 'integer',
        ];
    }

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function routePoints()
    {
        return $this->hasMany(RoutePoint::class);
    }
}
