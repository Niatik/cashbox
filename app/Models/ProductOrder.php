<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Events\ProductOrderDeleting;
use Database\Factories\ProductOrderFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'employee_id',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'price' => MoneyCast::class,
            'sum' => MoneyCast::class,
            'options' => 'array',
        ];
    }

    protected function netSum(): Attribute
    {
        return Attribute::get(fn (): float => $this->price * $this->quantity);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    protected $dispatchesEvents = [
        'deleting' => ProductOrderDeleting::class,
    ];
}
