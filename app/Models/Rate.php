<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'salary',
    ];

    protected $casts = [
        'salary' => MoneyCast::class,
    ];

    public function jobTitles(): BelongsToMany
    {
        return $this->belongsToMany(JobTitle::class);
    }

    public function rateRatios(): HasMany
    {
        return $this->hasMany(RateRatio::class);
    }
}
