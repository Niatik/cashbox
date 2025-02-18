<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Listeners\PaymentCreated;
use App\Listeners\PaymentDeleted;
use App\Listeners\PaymentUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_date',
        'payment_cash_amount',
        'payment_cashless_amount',
    ];

    protected $casts = [
        'payment_cash_amount' => MoneyCast::class,
        'payment_cashless_amount' => MoneyCast::class,
    ];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected $dispatchesEvents = [
        'created' => PaymentCreated::class,
        'updated' => PaymentUpdated::class,
        'deleted' => PaymentDeleted::class,
    ];

}
