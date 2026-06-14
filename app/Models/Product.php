<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
        ];
    }

    public function productOrders(): HasMany
    {
        return $this->hasMany(ProductOrder::class);
    }
}
