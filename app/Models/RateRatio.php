<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateRatio extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_id',
        'ratio',
        'ratio_from',
        'ratio_to',
    ];

    protected $casts = [
        'ratio' => MoneyCast::class,
    ];


    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
}
