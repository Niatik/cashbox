<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Events\SalaryWorkSessionCreated;
use App\Events\SalaryWorkSessionDeleted;
use App\Events\SalaryWorkSessionUpdated;
use Database\Factories\SalaryWorkSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryWorkSession extends Model
{
    /** @use HasFactory<SalaryWorkSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'work_session_id',
        'income_total',
        'expense_total',
        'salary_total',
        'salary_amount',
        'salary_amount_cashless',
    ];

    protected $casts = [
        'income_total' => MoneyCast::class,
        'expense_total' => MoneyCast::class,
        'salary_total' => MoneyCast::class,
        'salary_amount' => MoneyCast::class,
        'salary_amount_cashless' => MoneyCast::class,
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
