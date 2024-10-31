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
        'service_id',
        'social_media_id',
        'time_order',
        'people_number',
        'status',
        'sum',
        'employee_id',
        'customer_id',
        'options',
    ];

    protected $casts = [
        'sum' => MoneyCast::class,
        'options' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
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
