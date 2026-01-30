<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    public function rates(): BelongsToMany
    {
        return $this->belongsToMany(Rate::class);
    }
}
