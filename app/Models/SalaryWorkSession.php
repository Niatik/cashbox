<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Events\SalaryWorkSessionCreated;
use App\Events\SalaryWorkSessionDeleted;
use App\Events\SalaryWorkSessionUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryWorkSession extends Model
{
    /** @use HasFactory<\Database\Factories\SalaryWorkSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'work_session_id',
        'income_total',
        'expense_total',
        'salary_total',
        'salary_amount',
        'is_cash',
    ];

    protected $casts = [
        'income_total' => MoneyCast::class,
        'expense_total' => MoneyCast::class,
        'salary_total' => MoneyCast::class,
        'salary_amount' => MoneyCast::class,
        'is_cash' => 'boolean',
    ];

    /** @var array<string, class-string> */
    protected $dispatchesEvents = [
        'created' => SalaryWorkSessionCreated::class,
        'updated' => SalaryWorkSessionUpdated::class,
        'deleted' => SalaryWorkSessionDeleted::class,
    ];

    public function workSession(): BelongsTo
    {
        return $this->belongsTo(WorkSession::class);
    }
}
