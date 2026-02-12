<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    public function salaryRates(): HasMany
    {
        return $this->hasMany(SalaryRate::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }
}
