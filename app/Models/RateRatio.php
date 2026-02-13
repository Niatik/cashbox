<?php

namespace App\Models;

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

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
}
