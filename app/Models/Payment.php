<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Events\PaymentCreated;
use App\Events\PaymentDeleted;
use App\Events\PaymentUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'payment_date',
        'payment_time',
        'payment_cash_amount',
        'payment_cashless_amount',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_time' => 'datetime:H:i:s',
        'payment_cash_amount' => MoneyCast::class,
        'payment_cashless_amount' => MoneyCast::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->payment_time)) {
                $payment->payment_time = now()->format('H:i:s');
            }
        });
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    protected $dispatchesEvents = [
        'created' => PaymentCreated::class,
        'deleted' => PaymentDeleted::class,
        'updated' => PaymentUpdated::class,
    ];
}
