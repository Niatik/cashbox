<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Database\Factories\BonusWorkSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusWorkSession extends Model
{
    /** @use HasFactory<BonusWorkSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'work_session_id',
        'amount',
        'bonus_type',
    ];

    protected $casts = [
        'amount' => MoneyCast::class,
    ];

    public function workSession(): BelongsTo
    {
        return $this->belongsTo(WorkSession::class);
    }
}
