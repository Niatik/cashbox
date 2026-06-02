<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Database\Factories\ProductOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOrder extends Model
{
    /** @use HasFactory<ProductOrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_date',
        'order_time',
        'product_id',
        'price',
        'quantity',
        'sum',
        'customer_id',
        'options',
    ];

    protected $casts = [
        'order_date' => 'date',
        'price' => MoneyCast::class,
        'sum' => MoneyCast::class,
        'options' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
