<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseWorkSession extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseWorkSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'work_session_id',
        'expense_type',
        'amount',
    ];

    protected $casts = [
        'amount' => MoneyCast::class,
    ];


    public function workSession(): BelongsTo
    {
        return $this->belongsTo(WorkSession::class);
    }

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }
}
