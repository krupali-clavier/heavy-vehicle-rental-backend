<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'client_id',
        'vehicle_owner_id',
        'rental_amount',
        'driver_fee',
        'delivery_fee',
        'platform_fee',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'due_date',
        'paid_date',
        'line_items',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rental_amount' => 'decimal:2',
            'driver_fee' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'date',
            'line_items' => 'array',
        ];
    }

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function vehicleOwner()
    {
        return $this->belongsTo(User::class, 'vehicle_owner_id');
    }
}
