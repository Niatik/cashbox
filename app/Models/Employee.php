<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'job_title_id',
        'employment_date',
        'user_id',
        'is_hidden',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }
}
