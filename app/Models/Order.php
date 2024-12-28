<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_date',
        'order_time',
        'price_id',
        'price_item_id',
        'social_media_id',
        'people_number',
        'status',
        'sum',
        'net_sum',
        'employee_id',
        'customer_id',
        'options',
        'is_paid',
    ];

    protected $casts = [
        'sum' => MoneyCast::class,
        'net_sum' => MoneyCast::class,
        'options' => 'array',
        'is_paid' => 'boolean',
    ];

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    public function price_item(): BelongsTo
    {
        return $this->belongsTo(PriceItem::class);
    }

    public function social_media(): BelongsTo
    {
        return $this->belongsTo(SocialMedia::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
