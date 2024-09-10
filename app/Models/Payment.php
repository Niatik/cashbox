<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_type_id',
        'payment_date',
        'payment_amount',
    ];

    protected $casts = [
        'payment_amount' => MoneyCast::class,
    ];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment_type(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }
}
