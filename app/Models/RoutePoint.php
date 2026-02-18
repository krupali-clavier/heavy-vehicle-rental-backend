<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'altitude',
        'accuracy',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'speed' => 'decimal:2',
            'heading' => 'decimal:2',
            'altitude' => 'decimal:2',
            'accuracy' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    // Relationships
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
