<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Events\BookingCreated;
use App\Events\BookingUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_date',
        'booking_time',
        'booking_price_items',
        'sum',
        'prepayment',
        'employee_id',
        'customer_id',
    ];

    protected $casts = [
        'sum' => MoneyCast::class,
        'prepayment' => MoneyCast::class,
        'booking_price_items' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orders(): HaSMany
    {
        return $this->hasMany(Order::class);
    }

    protected $dispatchesEvents = [
        'created' => BookingCreated::class,
        'updated' => BookingUpdated::class,
    ];
}
