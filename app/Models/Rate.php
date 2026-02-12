<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'salary',
        'job_title_id',
    ];

    protected $casts = [
        'salary' => MoneyCast::class,
    ];

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function rateRatios(): HasMany
    {
        return $this->hasMany(RateRatio::class);
    }
}
