<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_order',
        'service_id',
        'social_media_id',
        'time_order',
        'people_number',
        'status',
        'sum',
    ];

    protected $casts = [
        'sum' => MoneyCast::class,
    ];



    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function social_media(): BelongsTo
    {
        return $this->belongsTo(SocialMedia::class);
    }
}
