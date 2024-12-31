<?php

namespace App\Models;

use App\Casts\ThousandthCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'price_id',
        'name_item',
        'factor',
    ];

    protected $casts = [
        'factor' => ThousandthCast::class,
    ];

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }
}
