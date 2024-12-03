<?php

namespace App\Models;

use App\Casts\ThousandthCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
