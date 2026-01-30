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
        'name',
        'ratio',
    ];

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
}
